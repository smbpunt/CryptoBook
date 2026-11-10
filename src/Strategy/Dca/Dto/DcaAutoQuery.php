<?php

namespace App\Strategy\Dca\Dto;

use App\Strategy\Dca\Enum\DcaRecurrence;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query string of the "DCA Auto" page: split a total amount into recurring positions.
 */
#[Exclude]
final readonly class DcaAutoQuery
{
    public const int MAX_COUNT = 1000;

    public function __construct(
        #[SerializedName('crypto')]
        #[Assert\Positive]
        public int $coinId,
        #[SerializedName('t')]
        public DcaRecurrence $recurrence,
        #[SerializedName('nb')]
        #[Assert\Range(min: 1, max: self::MAX_COUNT)]
        public int $count,
        #[SerializedName('total')]
        #[Assert\Positive]
        public float $total,
        // Value of an <input type="datetime-local">.
        #[SerializedName('d')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i'])]
        public \DateTimeImmutable $firstDate,
    ) {
    }
}
