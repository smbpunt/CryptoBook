<?php

namespace App\Shared\Schedule;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Every recurring task of the app, run by `messenger:consume scheduler_default`.
 */
#[AsSchedule]
final readonly class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function getSchedule(): Schedule
    {
        return new Schedule()
            // A restarted worker runs the last missed occurrence of each task, not all of them.
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
            ->add(
                // Prices first, the snapshot 5 minutes later uses them.
                RecurringMessage::every('1 hour', new RunCommandMessage('crypto:updatePrices'), from: '00:00'),
                RecurringMessage::every('1 hour', new RunCommandMessage('wallet:snapshot'), from: '00:05'),
                RecurringMessage::every('1 day', new RunCommandMessage('crypto:updateDatas'), from: '04:30'),
                // Fixer free plan: 100 requests a month, one per currency per run.
                RecurringMessage::every('1 day', new RunCommandMessage('fiat:updateRates'), from: '06:00'),
            );
    }
}
