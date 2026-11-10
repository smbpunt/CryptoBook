<?php

namespace App\Dashboard\Cache;

use App\Dashboard\Builder\DashboardBuilder;
use App\Dashboard\Dto\DashboardData;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Dashboard data per user, built on the first read and kept until invalidated.
 */
final readonly class DashboardCache
{
    private const string TAG_ALL = 'dashboard';

    public function __construct(
        private DashboardBuilder $builder,
        #[Target('dashboard.cache')]
        private TagAwareCacheInterface $cache,
    ) {
    }

    public function get(User $user): DashboardData
    {
        $userId = (int) $user->getId();

        return $this->cache->get('dashboard_'.$userId, function (ItemInterface $item) use ($user, $userId): DashboardData {
            $item->tag([self::TAG_ALL, self::userTag($userId)]);

            return $this->builder->build($user);
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

    private static function userTag(int $userId): string
    {
        return 'dashboard_user_'.$userId;
    }
}
