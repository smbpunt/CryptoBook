<?php

namespace App\Admin\Controller;

use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\RatesUnavailableException;
use App\FiatCurrency\Updater\FiatRateUpdater;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * A currency is created with its rates, or not at all: the converter can always use it.
 *
 * @extends AbstractCrudController<FiatCurrency>
 */
class FiatCurrencyCrudController extends AbstractCrudController
{
    public function __construct(private readonly FiatRateUpdater $fiatRateUpdater)
    {
    }

    public static function getEntityFqcn(): string
    {
        return FiatCurrency::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle');
        // Its rates and the deposits made in it depend on it.
        yield TextField::new('fixerKey')->setDisabled(Crud::PAGE_EDIT === $pageName);
        yield TextField::new('symbol');
        yield DateField::new('updatedAt')->hideOnForm();
        yield ArrayField::new('rates')->hideOnForm();
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        return parent::createNewFormBuilder($entityDto, $formOptions, $context)
            ->addEventListener(FormEvents::POST_SUBMIT, $this->fetchRates(...));
    }

    private function fetchRates(FormEvent $event): void
    {
        $form = $event->getForm();
        $currency = $form->getData();
        if (!$currency instanceof FiatCurrency || !$form->isValid()) {
            return;
        }

        try {
            $this->fiatRateUpdater->update($currency);
        } catch (RatesUnavailableException $exception) {
            $form->get('fixerKey')->addError(new FormError(\sprintf('Fixer ne donne pas de taux pour cette devise : %s', $exception->getMessage())));
        }
    }
}
