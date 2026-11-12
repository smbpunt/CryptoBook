<?php

namespace App\Admin\Controller;

use App\Blockchain\Entity\Dapp;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Dapp>
 */
class DappCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Dapp::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle');
        yield TextField::new('url');
        yield AssociationField::new('blockchain', 'Blockchain');
    }
}
