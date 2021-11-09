<?php

namespace App\Controller;

use App\Entity\Nft;
use App\Form\NftType;
use App\Repository\NftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/nft")
 */
class NftController extends AbstractController
{
    /**
     * @Route("/", name="nft_index", methods={"GET"})
     */
    public function index(NftRepository $nftRepository): Response
    {
        $nfts = $nftRepository->findBySold($this->getUser(), false);
        $nfts_sold = $nftRepository->findBySold($this->getUser(), true);
        $ar_nft_sold = [];
        $total_benef = 0;
        foreach ($nfts_sold as $nft) {
            $benef = $nft->getPriceSoldUsd() * (1 - ($nft->getPercentSaleFees() ?? 0.) / 100) - $nft->getPriceUsd();
            $total_benef += $benef;
            $ar_nft_sold[] = [
                'nft' => $nft,
                'benef' => $benef
            ];
        }

        return $this->render('nft/index.html.twig', [
            'nfts' => $nfts,
            'nfts_sold' => $ar_nft_sold,
            'total_benef' => $total_benef,
        ]);
    }

    /**
     * @Route("/new", name="nft_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $nft = new Nft($this->getUser());
        $form = $this->createForm(NftType::class, $nft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($nft);
            $entityManager->flush();

            return $this->redirectToRoute('nft_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('nft/new.html.twig', [
            'nft' => $nft,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="nft_show", methods={"GET"})
     */
    public function show(Nft $nft): Response
    {
        return $this->render('nft/show.html.twig', [
            'nft' => $nft,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="nft_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Nft $nft): Response
    {
        $form = $this->createForm(NftType::class, $nft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('nft_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('nft/edit.html.twig', [
            'nft' => $nft,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="nft_delete", methods={"POST"})
     */
    public function delete(Request $request, Nft $nft): Response
    {
        if ($this->isCsrfTokenValid('delete' . $nft->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($nft);
            $entityManager->flush();
        }

        return $this->redirectToRoute('nft_index', [], Response::HTTP_SEE_OTHER);
    }
}
