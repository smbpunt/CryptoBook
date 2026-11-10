<?php

namespace App\Tests\Nft\Repository;

use App\Nft\Entity\Nft;
use App\Nft\Repository\NftRepository;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class NftRepositoryTest extends KernelTestCase
{
    public function testFindHeldAndSold(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $data = Dataset::load($em);
        $sold = new Nft($data->user)->setCollection('Sold')->setBlockchain($data->ethereum)->setCryptocurrency($data->eth)
            ->setPriceCrypto(1)->setPriceUsd(1000)->setSoldOn(new \DateTimeImmutable())->setPriceSoldUsd(1500);
        $em->persist($sold);
        $em->flush();
        $repository = self::getContainer()->get(NftRepository::class);

        self::assertSame([$data->nft], $repository->findHeld($data->user));
        self::assertSame([$sold], $repository->findSold($data->user));
        self::assertSame([], $repository->findHeld($data->admin));
    }
}
