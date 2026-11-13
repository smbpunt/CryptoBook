<?php

namespace App\Shared\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A date up to tomorrow. PHP runs in UTC and the date comes from the browser:
 * tomorrow is accepted so that "today" is never refused around midnight.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class NotInFuture extends Assert\Compound
{
    /**
     * @param array<string, mixed> $options
     *
     * @return list<Constraint>
     */
    protected function getConstraints(array $options): array
    {
        return [new Assert\LessThanOrEqual('tomorrow', message: 'La date ne peut pas être dans le futur.')];
    }
}
