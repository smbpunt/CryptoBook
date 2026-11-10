<?php

namespace App\ProjectMonitoring\Controller;

use App\ProjectMonitoring\Entity\ProjectMonitoring;
use App\ProjectMonitoring\Form\ProjectMonitoringType;
use App\ProjectMonitoring\Repository\ProjectMonitoringRepository;
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

#[Route('/project/monitoring')]
class ProjectMonitoringController extends AbstractController
{
    #[Route('/', name: 'app_project_monitoring_index', methods: ['GET'])]
    public function index(ProjectMonitoringRepository $projectMonitoringRepository, #[CurrentUser] User $user): Response
    {
        return $this->render('project_monitoring/index.html.twig', [
            'project_monitorings' => $projectMonitoringRepository->findBy(['owner' => $user]),
        ]);
    }

    #[Route('/new', name: 'app_project_monitoring_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProjectMonitoringRepository $projectMonitoringRepository, #[CurrentUser] User $user): Response
    {
        $projectMonitoring = new ProjectMonitoring($user);
        $form = $this->createForm(ProjectMonitoringType::class, $projectMonitoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectMonitoringRepository->add($projectMonitoring, true);

            return $this->redirectToRoute('app_project_monitoring_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_monitoring/new.html.twig', [
            'project_monitoring' => $projectMonitoring,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_monitoring_edit', methods: ['GET', 'POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'projectMonitoring')]
    public function edit(Request $request, ProjectMonitoring $projectMonitoring, ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        $form = $this->createForm(ProjectMonitoringType::class, $projectMonitoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectMonitoringRepository->add($projectMonitoring, true);

            return $this->redirectToRoute('app_project_monitoring_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_monitoring/edit.html.twig', [
            'project_monitoring' => $projectMonitoring,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_monitoring_delete', methods: ['POST'])]
    #[IsGranted(OwnerVoter::OWNER, 'projectMonitoring')]
    #[IsCsrfTokenValid(new Expression('"delete" ~ args["projectMonitoring"].getId()'))]
    public function delete(ProjectMonitoring $projectMonitoring, ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        $projectMonitoringRepository->remove($projectMonitoring, true);

        return $this->redirectToRoute('app_project_monitoring_index', [], Response::HTTP_SEE_OTHER);
    }
}
