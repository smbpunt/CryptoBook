<?php

namespace App\Tests\Shared\Schedule;

use App\Shared\Schedule\MainSchedule;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;

final class MainScheduleTest extends KernelTestCase
{
    public function testSchedulesTheDataUpdates(): void
    {
        $schedule = self::getContainer()->get(MainSchedule::class)->getSchedule();

        $commands = [];
        foreach ($schedule->getRecurringMessages() as $recurringMessage) {
            $commands[$this->commandOf($recurringMessage)] = (string) $recurringMessage->getTrigger();
        }

        self::assertSame([
            'crypto:updatePrices' => '*/15 * * * *',
            'wallet:snapshot' => '1 * * * *',
            'crypto:updateDatas' => '5 3 * * *',
            'fiat:updateRates' => '0 6 * * *',
        ], $commands);
        self::assertNotNull($schedule->getState());
        self::assertTrue($schedule->shouldProcessOnlyLastMissedRun());
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
