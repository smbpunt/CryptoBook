<?php

namespace App\Controller\Admin;

use App\Entity\TypeProject;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @extends AbstractCrudController<TypeProject>
 */
class TypeProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeProject::class;
    }
}
