<?php

namespace App\Shared\Schedule;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Recurring data updates, run by the worker: messenger:consume scheduler_default.
 * Messages are handled one by one, so the snapshot at :01 runs after the price update at :00.
 */
#[AsSchedule]
final readonly class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        #[Autowire(param: 'kernel.debug')]
        private bool $debug,
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();
        // Prod: after a restart, a missed task runs once. Not in dev, where `make scheduler`
        // would replay fiat:updateRates on every start and burn the Fixer quota.
        if (!$this->debug) {
            $schedule->stateful($this->cache)->processOnlyLastMissedRun(true);
        }

        return $schedule->add(
            RecurringMessage::cron('*/15 * * * *', new RunCommandMessage('crypto:updatePrices')),
            RecurringMessage::cron('1 * * * *', new RunCommandMessage('wallet:snapshot')),
            RecurringMessage::cron('5 3 * * *', new RunCommandMessage('crypto:updateDatas')),
            RecurringMessage::cron('0 6 * * *', new RunCommandMessage('fiat:updateRates')),
        );
    }
}
