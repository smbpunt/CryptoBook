<?php

namespace App\Controller;

use App\Entity\Nft;
use App\Form\NftType;
use App\Repository\NftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nft')]
class NftController extends AbstractController
{
    #[Route('/', name: 'app_nft_index', methods: ['GET'])]
    public function index(NftRepository $nftRepository): Response
    {
        $nfts = $nftRepository->findBySold($this->getUser(), false);
        $nfts_sold = $nftRepository->findBySold($this->getUser(), true);
        $total_benef = 0;
        foreach ($nfts_sold as $nft) {
            $total_benef += $nft->getBenefice();
        }

        return $this->render('nft/index.html.twig', [
            'nfts' => $nfts,
            'nfts_sold' => $nfts_sold,
            'total_benef' => $total_benef,
        ]);
    }

    #[Route('/new', name: 'app_nft_new', methods: ['GET', 'POST'])]
    public function new(Request $request, NftRepository $nftRepository): Response
    {
        $nft = new Nft($this->getUser());
        $form = $this->createForm(NftType::class, $nft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nftRepository->add($nft, true);

            return $this->redirectToRoute('app_nft_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('nft/new.html.twig', [
            'nft' => $nft,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/infos', name: 'app_nft_ajax_infos', methods: ['POST'])]
    public function ajax(Nft $nft): Response
    {
        if ($nft->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('nft/show.html.twig', [
            'nft' => $nft,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_nft_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Nft $nft, NftRepository $nftRepository): Response
    {
        if ($nft->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_nft_index');
        }

        $form = $this->createForm(NftType::class, $nft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nftRepository->add($nft, true);

            return $this->redirectToRoute('app_nft_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('nft/edit.html.twig', [
            'nft' => $nft,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nft_delete', methods: ['POST'])]
    public function delete(Request $request, Nft $nft, NftRepository $nftRepository): Response
    {
        if ($nft->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_nft_index');
        }

        if ($this->isCsrfTokenValid('delete'.$nft->getId(), $request->request->get('_token'))) {
            $nftRepository->remove($nft, true);
        }

        return $this->redirectToRoute('app_nft_index', [], Response::HTTP_SEE_OTHER);
    }
}
