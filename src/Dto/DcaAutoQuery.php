<?php

namespace App\Dto;

use App\Form\DcaAutoType;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Serializer\Attribute\SerializedName;
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
        #[Assert\Choice(choices: DcaAutoType::RECURRENCES)]
        public string $recurrence,
        #[SerializedName('nb')]
        #[Assert\Range(min: 1, max: self::MAX_COUNT)]
        public int $count,
        #[SerializedName('total')]
        #[Assert\Positive]
        public float $total,
        #[SerializedName('d')]
        #[Assert\NotBlank]
        public string $firstDate,
    ) {
    }
}
