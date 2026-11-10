<?php

namespace App\Deposit\Controller;

use App\Deposit\Entity\Deposit;
use App\Deposit\Form\DepositType;
use App\Deposit\Repository\DepositRepository;
use App\Entity\User;
use App\Security\Voter\OwnerVoter;
use App\Service\DepositService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/deposit')]
class DepositController extends AbstractController
{
    #[Route('/', name: 'app_deposit_index', methods: ['GET'])]
    public function index(DepositService $depositService, DepositRepository $depositRepository, #[CurrentUser] User $user): Response
    {
        $deposits = $depositRepository->findBy([
            'owner' => $user,
        ], [
            'depositedAt' => \SortDirection::Ascending,
        ]);

        $totalUsd = $depositService->getTotalDepositUsd($user);

        return $this->render('deposit/index.html.twig', [
            'deposits' => $deposits,
            'totalUsd' => $totalUsd,
        ]);
    }

    #[Route('/new', name: 'app_deposit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DepositRepository $depositRepository, #[CurrentUser] User $user): Response
    {
        $deposit = new Deposit($user);
        $form = $this->createForm(DepositType::class, $deposit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $depositRepository->add($deposit, true);

            return $this->redirectToRoute('app_deposit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('deposit/new.html.twig', [
            'deposit' => $deposit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_deposit_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'deposit')]
    public function edit(Request $request, Deposit $deposit, DepositRepository $depositRepository): Response
    {
        $form = $this->createForm(DepositType::class, $deposit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $depositRepository->add($deposit, true);

            return $this->redirectToRoute('app_deposit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('deposit/edit.html.twig', [
            'deposit' => $deposit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_deposit_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'deposit')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["deposit"].getId()'))]
    public function delete(Deposit $deposit, DepositRepository $depositRepository): Response
    {
        $depositRepository->remove($deposit, true);

        return $this->redirectToRoute('app_deposit_index', [], Response::HTTP_SEE_OTHER);
    }
}
