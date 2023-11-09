<?php

namespace App\Controller;

use App\Entity\Loan;
use App\Form\LoanType;
use App\Repository\LoanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/loan')]
class LoanController extends AbstractController
{
    #[Route('/', name: 'app_loan_index', methods: ['GET'])]
    public function index(LoanRepository $loanRepository): Response
    {
        $loans = $loanRepository->findBy([
            'owner' => $this->getUser()
        ]);

        $total = $loanRepository->getTotal($this->getUser());

        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'total' => $total
        ]);
    }

    #[Route('/{id}/infos', name: 'app_loan_ajax_infos', methods: ['POST'])]
    public function ajax(Loan $loan): Response
    {
        if ($loan->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('loan/_informations.html.twig', [
            'loan' => $loan,
        ]);
    }

    #[Route('/new', name: 'app_loan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LoanRepository $loanRepository): Response
    {
        $loan = new Loan($this->getUser());
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $loanRepository->add($loan, true);

            return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('loan/new.html.twig', [
            'loan' => $loan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_loan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Loan $loan, LoanRepository $loanRepository): Response
    {
        if ($loan->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_loan_index');
        }

        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$request->isXmlHttpRequest()) {
            $loanRepository->add($loan, true);

            return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('loan/edit.html.twig', [
            'loan' => $loan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_loan_delete', methods: ['POST'])]
    public function delete(Request $request, Loan $loan, LoanRepository $loanRepository): Response
    {
        if ($loan->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_loan_index');
        }

        if ($this->isCsrfTokenValid('delete'.$loan->getId(), $request->request->get('_token'))) {
            $loanRepository->remove($loan, true);
        }

        return $this->redirectToRoute('app_loan_index', [], Response::HTTP_SEE_OTHER);
    }
}
