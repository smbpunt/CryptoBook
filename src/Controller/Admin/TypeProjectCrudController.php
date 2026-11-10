<?php

namespace App\Controller\Admin;

use App\ProjectMonitoring\Entity\TypeProject;
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
