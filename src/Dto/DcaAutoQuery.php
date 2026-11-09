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
    public function __construct(
        #[SerializedName('crypto')]
        #[Assert\Positive]
        public int $coinId,
        #[SerializedName('t')]
        #[Assert\Choice(choices: DcaAutoType::RECURRENCES)]
        public string $recurrence,
        #[SerializedName('nb')]
        #[Assert\Positive]
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
