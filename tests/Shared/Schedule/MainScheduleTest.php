<?php

namespace App\Tests\Shared\Schedule;

use App\Shared\Schedule\MainSchedule;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;

final class MainScheduleTest extends KernelTestCase
{
    public function testPlansEveryDataRefreshAndTheSnapshot(): void
    {
        $schedule = self::getContainer()->get(MainSchedule::class)->getSchedule();
        // Triggers start today: check the runs following tomorrow 10:42.
        $after = new \DateTimeImmutable('tomorrow 10:42');
        $day = $after->format('Y-m-d');
        $nextDay = $after->modify('+1 day')->format('Y-m-d');

        $nextRuns = [];
        foreach ($schedule->getRecurringMessages() as $recurringMessage) {
            $nextRuns[$this->command($recurringMessage)] = $recurringMessage->getTrigger()->getNextRunDate($after)?->format('Y-m-d H:i');
        }

        self::assertSame([
            'crypto:updatePrices' => "{$day} 11:00",
            'wallet:snapshot' => "{$day} 11:05",
            'crypto:updateDatas' => "{$nextDay} 04:30",
            'fiat:updateRates' => "{$nextDay} 06:00",
        ], $nextRuns);
        self::assertNotNull($schedule->getState());
    }

    public function testEveryScheduledCommandExists(): void
    {
        $schedule = self::getContainer()->get(MainSchedule::class)->getSchedule();
        $application = new Application(self::$kernel);

        foreach ($schedule->getRecurringMessages() as $recurringMessage) {
            self::assertTrue($application->has($this->command($recurringMessage)));
        }
    }

    private function command(RecurringMessage $recurringMessage): string
    {
        $context = new MessageContext('default', $recurringMessage->getId(), $recurringMessage->getTrigger(), new \DateTimeImmutable());
        $message = [...$recurringMessage->getMessages($context)][0];
        self::assertInstanceOf(RunCommandMessage::class, $message);

        return $message->input;
    }
}
