<?php

namespace App\User\Entity;

/**
 * Data that belongs to a user: only its owner can read, edit or delete it.
 */
interface OwnedInterface
{
    public function getOwner(): User;
}
