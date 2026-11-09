<?php

namespace App\Tests\Shared\Schedule;

use App\Shared\Schedule\MainSchedule;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

final class MainScheduleTest extends KernelTestCase
{
    private Schedule $schedule;

    protected function setUp(): void
    {
        $this->schedule = self::getContainer()->get(MainSchedule::class)->getSchedule();
    }

    public function testSnapshotRunsEveryHourAfterThePriceUpdate(): void
    {
        [$prices, $nextPrices] = $this->nextRuns('crypto:updatePrices');
        [$snapshot, $nextSnapshot] = $this->nextRuns('wallet:snapshot', after: $prices);

        self::assertSame(3600, $nextPrices->getTimestamp() - $prices->getTimestamp());
        self::assertSame(3600, $nextSnapshot->getTimestamp() - $snapshot->getTimestamp());
        self::assertLessThan($nextPrices, $snapshot);
    }

    public function testDataAndRatesAreRefreshedOnceADay(): void
    {
        foreach (['crypto:updateDatas', 'fiat:updateRates'] as $command) {
            [$first, $second] = $this->nextRuns($command);
            self::assertSame(86400, $second->getTimestamp() - $first->getTimestamp(), $command);
        }
    }

    public function testRestartedWorkerRunsOnlyTheLastMissedOccurrence(): void
    {
        // Replaying every missed run would burn the Fixer quota.
        self::assertNotNull($this->schedule->getState());
        self::assertTrue($this->schedule->shouldProcessOnlyLastMissedRun());
    }

    public function testEveryScheduledCommandExists(): void
    {
        $application = new Application(self::$kernel);

        foreach ($this->schedule->getRecurringMessages() as $recurringMessage) {
            self::assertTrue($application->has($this->command($recurringMessage)));
        }
    }

    /**
     * The two runs of a command following a date, tomorrow noon by default (triggers start today).
     *
     * @return array{\DateTimeImmutable, \DateTimeImmutable}
     */
    private function nextRuns(string $command, \DateTimeImmutable $after = new \DateTimeImmutable('tomorrow 12:00')): array
    {
        foreach ($this->schedule->getRecurringMessages() as $recurringMessage) {
            if ($this->command($recurringMessage) === $command) {
                $trigger = $recurringMessage->getTrigger();
                $first = $trigger->getNextRunDate($after) ?? self::fail("{$command} never runs");
                $second = $trigger->getNextRunDate($first) ?? self::fail("{$command} runs once");

                return [$first, $second];
            }
        }

        self::fail("{$command} is not scheduled");
    }

    private function command(RecurringMessage $recurringMessage): string
    {
        $context = new MessageContext('default', $recurringMessage->getId(), $recurringMessage->getTrigger(), new \DateTimeImmutable());
        $message = [...$recurringMessage->getMessages($context)][0];
        self::assertInstanceOf(RunCommandMessage::class, $message);

        return $message->input;
    }
}
