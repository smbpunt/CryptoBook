<?php

namespace App\Controller\Admin;

use App\Entity\Blockchain;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BlockchainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Blockchain::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle');
        yield AssociationField::new('coin', 'Coin')
            ->autocomplete();
    }
}
