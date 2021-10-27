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
