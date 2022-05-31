<?php

namespace App\Controller\Admin;

use App\Entity\DepositType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DepositTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DepositType::class;
    }
}
