<?php

namespace App\Position\Controller;

use App\Position\Builder\PositionGroupBuilder;
use App\Position\Calculator\ExitPlanCalculator;
use App\Position\Entity\Position;
use App\Position\Form\PositionType;
use App\Position\Repository\PositionRepository;
use App\User\Entity\User;
use App\User\Voter\OwnerVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/position')]
class PositionController extends AbstractController
{
    #[Route('/', name: 'app_position_index', methods: ['GET'])]
    public function index(PositionRepository $positionRepository, PositionGroupBuilder $positionGroupBuilder, #[CurrentUser] User $user): Response
    {
        return $this->render('position/index.html.twig', [
            'groups' => $positionGroupBuilder->build($positionRepository->findOpen($user, stable: false)),
            'positions_stable' => $positionRepository->findOpen($user, stable: true),
            'positions_closed' => $positionRepository->findClosed($user),
        ]);
    }

    #[Route('/{id}/infos', name: 'app_position_ajax_infos', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'position')]
    public function ajax(Position $position, ExitPlanCalculator $exitPlanCalculator): Response
    {
        return $this->render('position/_strategy_position.html.twig', [
            'position' => $position,
            'exitPlan' => $position->getStrategies()->isEmpty() ? null : $exitPlanCalculator->calculate($position),
        ]);
    }

    #[Route('/new', name: 'app_position_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PositionRepository $positionRepository, #[CurrentUser] User $user): Response
    {
        $position = new Position($user);
        $form = $this->createForm(PositionType::class, $position);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $positionRepository->add($position, true);

            return $this->redirectAfterSave($form);
        }

        return $this->render('position/new.html.twig', [
            'position' => $position,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_position_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'position')]
    public function edit(Request $request, Position $position, PositionRepository $positionRepository): Response
    {
        $form = $this->createForm(PositionType::class, $position);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $positionRepository->add($position, true);

            return $this->redirectAfterSave($form);
        }

        return $this->render('position/edit.html.twig', [
            'position' => $position,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_position_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'position')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["position"].getId()'))]
    public function delete(Position $position, PositionRepository $positionRepository): Response
    {
        $positionRepository->remove($position, true);

        return $this->redirectToRoute('app_position_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Valider & continuer opens the creation form, Valider the list.
     *
     * @param FormInterface<Position> $form
     */
    private function redirectAfterSave(FormInterface $form): Response
    {
        $next = $form->get('submitAndNext');
        \assert($next instanceof ClickableInterface);

        return $this->redirectToRoute($next->isClicked() ? 'app_position_new' : 'app_position_index', [], Response::HTTP_SEE_OTHER);
    }
}
