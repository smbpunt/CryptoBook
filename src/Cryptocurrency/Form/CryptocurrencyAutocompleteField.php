<?php

namespace App\Cryptocurrency\Form;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

/**
 * Coin picker searched over AJAX (`/autocomplete/cryptocurrency_autocomplete_field`): the list is too long to be rendered in each form.
 * Biggest market caps first, so the preloaded list shows the usual coins. Then by id: a stable order for the pages of the dropdown.
 *
 * @extends AbstractType<Cryptocurrency|null>
 */
#[AsEntityAutocompleteField]
class CryptocurrencyAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Cryptocurrency::class,
            'placeholder' => 'Choisir une crypto',
            'searchable_fields' => ['symbol', 'libelle'],
            'query_builder' => static fn (CryptocurrencyRepository $repository): QueryBuilder => $repository->createQueryBuilder('c')->orderBy('c.mcapUsd', \SortDirection::Descending)->addOrderBy('c.id'),
            'security' => 'ROLE_USER',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
