<?php

namespace App\Tests\Position\Repository;

use App\Position\Entity\Position;
use App\Position\Repository\PositionRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PositionRepositoryTest extends KernelTestCase
{
    public function testFindOpenAndClosed(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);
        $eth = new Position($data->user)->setCoin($data->eth)->setNbCoins(1)->setEntryCost(1000);
        $closed = new Position($data->user)->setCoin($data->eth)->setNbCoins(1)->setEntryCost(1000)->setIsOpened(false);
        $closedStable = new Position($data->user)->setCoin($data->usdc)->setNbCoins(10)->setEntryCost(10)->setIsOpened(false);
        $em->persist($eth);
        $em->persist($closed);
        $em->persist($closedStable);
        $em->flush();
        $repository = self::getContainer()->get(PositionRepository::class);

        // Biggest market cap first.
        self::assertSame([$data->position, $eth], $repository->findOpen($data->user, stable: false));
        self::assertCount(1, $repository->findOpen($data->user, stable: true));
        self::assertEqualsCanonicalizing([$closed, $closedStable], $repository->findClosed($data->user));
        self::assertSame([], $repository->findOpen($data->admin, stable: false));
    }
}
