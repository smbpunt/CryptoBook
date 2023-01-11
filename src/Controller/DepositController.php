<?php

namespace App\Controller;

use App\Entity\Deposit;
use App\Form\DepositType;
use App\Repository\DepositRepository;
use App\Service\DepositService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/deposit')]
class DepositController extends AbstractController
{
    #[Route('/', name: 'app_deposit_index', methods: ['GET'])]
    public function index(DepositService $depositService, DepositRepository $depositRepository): Response
    {
        $deposits =  $depositRepository->findBy([
            'owner' => $this->getUser()
        ], [
            'depositedAt' => 'ASC'
        ]);

        $totalUsd = $depositService->getTotalDepositUsdCurrentUser();

        return $this->render('deposit/index.html.twig', [
            'deposits' => $deposits,
            'totalUsd' => $totalUsd,
        ]);
    }

    #[Route('/new', name: 'app_deposit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DepositRepository $depositRepository): Response
    {
        $deposit = new Deposit($this->getUser());
        $form = $this->createForm(DepositType::class, $deposit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $depositRepository->add($deposit, true);

            return $this->redirectToRoute('app_deposit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('deposit/new.html.twig', [
            'deposit' => $deposit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_deposit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Deposit $deposit, DepositRepository $depositRepository): Response
    {
        if ($deposit->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_deposit_index');
        }

        $form = $this->createForm(DepositType::class, $deposit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $depositRepository->add($deposit, true);

            return $this->redirectToRoute('app_deposit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('deposit/edit.html.twig', [
            'deposit' => $deposit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_deposit_delete', methods: ['POST'])]
    public function delete(Request $request, Deposit $deposit, DepositRepository $depositRepository): Response
    {
        if ($deposit->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_deposit_index');
        }

        if ($this->isCsrfTokenValid('delete'.$deposit->getId(), $request->request->get('_token'))) {
            $depositRepository->remove($deposit, true);
        }

        return $this->redirectToRoute('app_deposit_index', [], Response::HTTP_SEE_OTHER);
    }
}
