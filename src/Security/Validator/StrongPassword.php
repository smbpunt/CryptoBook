<?php

namespace App\Security\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Password rules shared by registration and password reset.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class StrongPassword extends Assert\Compound
{
    public const string HELP = 'Au moins 14 caractères mêlant trois types (minuscules, majuscules, chiffres, symboles), ou une phrase de passe.';

    /**
     * @param array<string, mixed> $options
     *
     * @return list<Constraint>
     */
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(message: 'Veuillez saisir un mot de passe.'),
            new Assert\Length(max: 4096, maxMessage: 'Le mot de passe ne doit pas dépasser {{ limit }} caractères.'),
            new Assert\PasswordStrength(
                minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
                message: 'Mot de passe trop faible : allongez-le ou utilisez une phrase de passe.',
            ),
        ];
    }
}
