<?php

namespace App\Controller\Admin;

use App\Entity\TypeProject;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TypeProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeProject::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
