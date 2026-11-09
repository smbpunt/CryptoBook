<?php

namespace App\Controller\Admin;

use App\Entity\Exchange;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @extends AbstractCrudController<Exchange>
 */
class ExchangeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Exchange::class;
    }
}
