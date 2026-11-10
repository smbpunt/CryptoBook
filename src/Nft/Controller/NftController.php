<?php

namespace App\Nft\Controller;

use App\Entity\User;
use App\Nft\Calculator\NftProfitCalculator;
use App\Nft\Entity\Nft;
use App\Nft\Form\NftType;
use App\Nft\Repository\NftRepository;
use App\Security\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/nft')]
class NftController extends AbstractController
{
    #[Route('/', name: 'app_nft_index', methods: ['GET'])]
    public function index(NftRepository $nftRepository, NftProfitCalculator $profitCalculator, #[CurrentUser] User $user): Response
    {
        $sold = $nftRepository->findSold($user);

        return $this->render('nft/index.html.twig', [
            'nfts' => $nftRepository->findHeld($user),
            'nfts_sold' => $sold,
            'total_benef' => $profitCalculator->totalUsd($sold),
        ]);
    }

    #[Route('/new', name: 'app_nft_new', methods: ['GET', 'POST'])]
    public function new(Request $request, NftRepository $nftRepository, #[CurrentUser] User $user): Response
    {
        $nft = new Nft($user);
        $form = $this->createForm(NftType::class, $nft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nftRepository->add($nft, true);

            return $this->redirectToRoute('app_nft_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nft/new.html.twig', [
            'nft' => $nft,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/infos', name: 'app_nft_ajax_infos', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'nft')]
    public function ajax(Nft $nft): Response
    {
        return $this->render('nft/show.html.twig', [
            'nft' => $nft,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_nft_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'nft')]
    public function edit(Request $request, Nft $nft, NftRepository $nftRepository): Response
    {
        $form = $this->createForm(NftType::class, $nft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nftRepository->add($nft, true);

            return $this->redirectToRoute('app_nft_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nft/edit.html.twig', [
            'nft' => $nft,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nft_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'nft')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["nft"].getId()'))]
    public function delete(Nft $nft, NftRepository $nftRepository): Response
    {
        $nftRepository->remove($nft, true);

        return $this->redirectToRoute('app_nft_index', [], Response::HTTP_SEE_OTHER);
    }
}
