<?php

namespace App\Controller\Admin;

use App\Entity\Cryptocurrency;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CryptocurrencyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cryptocurrency::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Cryptocurrency')
            ->setEntityLabelInPlural('Cryptocurrencies')
            ->setSearchFields(['libelle']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('urlImgThumb')->hideOnForm();
        yield BooleanField::new('isStable');
        yield TextField::new('symbol')->hideOnForm();
        yield TextField::new('libelle')->hideOnForm();
        yield TextField::new('libelleCoingecko')->hideOnIndex();
        yield ColorField::new('color')->hideOnIndex();
        yield MoneyField::new('priceUsd')->setCurrency('USD')->setStoredAsCents(false)->hideOnForm();
        yield MoneyField::new('mcapUsd')->setCurrency('USD')->setStoredAsCents(false)->hideOnForm();
        yield MoneyField::new('priceEur')->setCurrency('EUR')->setStoredAsCents(false)->hideOnForm()->hideOnIndex();
        yield MoneyField::new('mcapEur')->setCurrency('EUR')->setStoredAsCents(false)->hideOnForm()->hideOnIndex();
    }
}
