<?php

namespace App\Controller\Admin;

use App\Entity\FiatCurrency;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FiatCurrencyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FiatCurrency::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle');
        yield TextField::new('fixerKey');
        yield TextField::new('symbol');
        yield DateField::new('updatedAt')->hideOnForm();
        yield ArrayField::new('rates')->hideOnForm();
    }
}
