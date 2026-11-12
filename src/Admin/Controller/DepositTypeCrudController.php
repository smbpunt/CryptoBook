<?php

namespace App\Admin\Controller;

use App\Deposit\Entity\DepositType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @extends AbstractCrudController<DepositType>
 */
class DepositTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DepositType::class;
    }
}
