<?php

namespace App\Loan\Controller;

use App\Loan\Entity\Loan;
use App\Loan\Form\LoanType;
use App\Loan\Repository\LoanRepository;
use App\User\Entity\User;
use App\User\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/loan')]
class LoanController extends AbstractController
{
    #[Route('/', name: 'app_loan_index', methods: ['GET'])]
    public function index(LoanRepository $loanRepository, #[CurrentUser] User $user): Response
    {
        $loans = $loanRepository->findBy([
            'owner' => $user,
        ]);

        $total = $loanRepository->sumValueUsd($user);

        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'total' => $total,
        ]);
    }

    #[Route('/{id}/infos', name: 'app_loan_ajax_infos', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'loan')]
    public function ajax(Loan $loan): Response
    {
        return $this->render('loan/_informations.html.twig', [
            'loan' => $loan,
        ]);
    }

    #[Route('/new', name: 'app_loan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LoanRepository $loanRepository, #[CurrentUser] User $user): Response
    {
        $loan = new Loan($user);
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $loanRepository->add($loan, true);

            return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('loan/new.html.twig', [
            'loan' => $loan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_loan_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'loan')]
    public function edit(Request $request, Loan $loan, LoanRepository $loanRepository): Response
    {
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $loanRepository->add($loan, true);

            return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('loan/edit.html.twig', [
            'loan' => $loan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_loan_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'loan')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["loan"].getId()'))]
    public function delete(Loan $loan, LoanRepository $loanRepository): Response
    {
        $loanRepository->remove($loan, true);

        return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
    }
}
