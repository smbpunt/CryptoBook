<?php

namespace App\Tests\Shared\Schedule;

use App\Shared\Schedule\MainSchedule;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;

final class MainScheduleTest extends KernelTestCase
{
    public function testSchedulesExistingCommands(): void
    {
        $schedule = self::getContainer()->get(MainSchedule::class)->getSchedule();
        $application = new Application(self::$kernel);

        $commands = [];
        foreach ($schedule->getRecurringMessages() as $recurringMessage) {
            $command = $this->commandOf($recurringMessage);
            self::assertTrue($application->has($command), "Unknown command $command");
            $commands[$command] = (string) $recurringMessage->getTrigger();
        }

        self::assertSame([
            'crypto:updatePrices' => '*/15 * * * *',
            'wallet:snapshot' => '1 * * * *',
            'crypto:updateDatas' => '5 3 * * *',
            'fiat:updateRates' => '0 6 * * *',
        ], $commands);
    }

    public function testReplaysOnlyTheLastMissedRunInProd(): void
    {
        $schedule = new MainSchedule(new ArrayAdapter(), false)->getSchedule();

        self::assertNotNull($schedule->getState());
        self::assertTrue($schedule->shouldProcessOnlyLastMissedRun());
    }

    public function testIsStatelessInDev(): void
    {
        self::assertNull(new MainSchedule(new ArrayAdapter(), true)->getSchedule()->getState());
    }

    private function commandOf(RecurringMessage $recurringMessage): string
    {
        $trigger = $recurringMessage->getTrigger();
        $context = new MessageContext('default', $recurringMessage->getId(), $trigger, new \DateTimeImmutable());
        $messages = [...$recurringMessage->getProvider()->getMessages($context)];
        self::assertCount(1, $messages);
        self::assertInstanceOf(RunCommandMessage::class, $messages[0]);

        return $messages[0]->input;
    }
}
