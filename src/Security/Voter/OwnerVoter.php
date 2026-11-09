<?php

namespace App\Security\Voter;

use App\Entity\OwnedInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Grants access to data only to the user who owns it.
 *
 * @extends Voter<string, OwnedInterface>
 */
final class OwnerVoter extends Voter
{
    public const string OWNER = 'OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::OWNER === $attribute && $subject instanceof OwnedInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        $owner = $subject->getOwner();
        if (!$user instanceof User || null === $owner || $owner->getId() !== $user->getId()) {
            $vote?->addReason('The user does not own this data.');

            return false;
        }

        return true;
    }
}
