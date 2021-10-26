<?php

namespace App\Controller\Admin;

use App\Entity\Cryptocurrency;
use App\Service\CryptocurrencyService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CryptocurrencyCrudController extends AbstractCrudController
{

    private CryptocurrencyService $cryptocurrencyService;

    public function __construct(CryptocurrencyService $cryptocurrencyService)
    {
        $this->cryptocurrencyService = $cryptocurrencyService;
    }


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

    public function configureActions(Actions $actions): Actions
    {
        $updateAll = Action::new('updateAll', 'Update all')
            ->linkToCrudAction('updateAllCryptocurrencies')
            ->addCssClass('btn btn-primary')
            ->setIcon('fas fa-sync-alt')
            ->createAsGlobalAction();

        return $actions
            ->addBatchAction($updateAll);
    }


    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('urlImgThumb')->hideOnForm();
        yield TextField::new('symbol')->hideOnForm();
        yield TextField::new('libelle')->hideOnForm();
        yield TextField::new('libelleCoingecko')->hideOnIndex();
        yield MoneyField::new('priceUsd')->setCurrency('USD')->setStoredAsCents(false)->hideOnForm();
        yield MoneyField::new('mcapUsd')->setCurrency('USD')->setStoredAsCents(false)->hideOnForm();
        yield MoneyField::new('priceEur')->setCurrency('EUR')->setStoredAsCents(false)->hideOnForm()->hideOnIndex();
        yield MoneyField::new('mcapEur')->setCurrency('EUR')->setStoredAsCents(false)->hideOnForm()->hideOnIndex();
    }

    public function updateAllCryptocurrencies(BatchActionDto $batchActionDto)
    {
        $this->cryptocurrencyService->updateAllCryptos();
        return $this->redirect($batchActionDto->getReferrerUrl());
    }
}
