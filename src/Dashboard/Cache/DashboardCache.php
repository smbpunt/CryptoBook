<?php

namespace App\Dashboard\Cache;

use App\Dashboard\Builder\DashboardBuilder;
use App\Dashboard\Builder\WalletHistoryBuilder;
use App\Dashboard\Dto\DashboardData;
use App\Dashboard\Dto\WalletHistory;
use App\User\Entity\User;
use App\Wallet\Enum\HistoryPeriod;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Dashboard data per user, built on the first read and kept until invalidated.
 */
final readonly class DashboardCache
{
    private const string TAG_ALL = 'dashboard';
    /** The pool outlives deploys (var/share): bump it when a cached DTO changes, old entries would not unserialize. */
    private const string VERSION = 'v4';

    public function __construct(
        private DashboardBuilder $builder,
        private WalletHistoryBuilder $historyBuilder,
        #[Target('dashboard.cache')]
        private TagAwareCacheInterface $cache,
    ) {
    }

    public function get(User $user): DashboardData
    {
        $userId = (int) $user->getId();

        return $this->cache->get(self::key($userId), function (ItemInterface $item) use ($user, $userId): DashboardData {
            $item->tag([self::TAG_ALL, self::userTag($userId)]);

            return $this->builder->build($user);
        });
    }

    /**
     * Ends on the current total of the dashboard: same tags, invalidated with it.
     */
    public function getHistory(User $user, HistoryPeriod $period): WalletHistory
    {
        $userId = (int) $user->getId();

        return $this->cache->get(self::historyKey($userId, $period), function (ItemInterface $item) use ($user, $userId, $period): WalletHistory {
            $item->tag([self::TAG_ALL, self::userTag($userId)]);

            return $this->historyBuilder->build($user, $period, $this->get($user)->totalUsd);
        });
    }

    /**
     * Every user, after a change of prices or rates.
     */
    public function invalidateAll(): void
    {
        $this->cache->invalidateTags([self::TAG_ALL]);
    }

    /**
     * @param list<int> $userIds
     */
    public function invalidateUsers(array $userIds): void
    {
        if ([] !== $userIds) {
            $this->cache->invalidateTags(array_map(self::userTag(...), $userIds));
        }
    }

    public static function key(int $userId): string
    {
        return 'dashboard_'.self::VERSION.'_'.$userId;
    }

    public static function historyKey(int $userId, HistoryPeriod $period): string
    {
        return 'dashboard_history_'.self::VERSION.'_'.$userId.'_'.$period->value;
    }

    private static function userTag(int $userId): string
    {
        return 'dashboard_user_'.$userId;
    }
}
