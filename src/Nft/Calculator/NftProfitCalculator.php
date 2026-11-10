<?php

namespace App\Nft\Calculator;

use App\Nft\Entity\Nft;

final readonly class NftProfitCalculator
{
    /**
     * Profit of the NFTs, sale fees deducted, in USD.
     *
     * @param iterable<Nft> $nfts
     */
    public function totalUsd(iterable $nfts): float
    {
        $total = 0.0;
        foreach ($nfts as $nft) {
            $total += $nft->getBenefice();
        }

        return $total;
    }
}
