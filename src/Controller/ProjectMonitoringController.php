<?php

namespace App\Controller;

use App\Entity\ProjectMonitoring;
use App\Form\ProjectMonitoringType;
use App\Repository\ProjectMonitoringRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/project/monitoring')]
class ProjectMonitoringController extends AbstractController
{
    #[Route('/', name: 'app_project_monitoring_index', methods: ['GET'])]
    public function index(ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        return $this->render('project_monitoring/index.html.twig', [
            'project_monitorings' => $projectMonitoringRepository->findBy(['owner' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_project_monitoring_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        $projectMonitoring = new ProjectMonitoring($this->getUser());
        $form = $this->createForm(ProjectMonitoringType::class, $projectMonitoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectMonitoringRepository->add($projectMonitoring, true);

            return $this->redirectToRoute('app_project_monitoring_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project_monitoring/new.html.twig', [
            'project_monitoring' => $projectMonitoring,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_monitoring_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProjectMonitoring $projectMonitoring, ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        if ($projectMonitoring->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_project_monitoring_index');
        }

        $form = $this->createForm(ProjectMonitoringType::class, $projectMonitoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectMonitoringRepository->add($projectMonitoring, true);

            return $this->redirectToRoute('app_project_monitoring_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project_monitoring/edit.html.twig', [
            'project_monitoring' => $projectMonitoring,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_monitoring_delete', methods: ['POST'])]
    public function delete(Request $request, ProjectMonitoring $projectMonitoring, ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        if ($projectMonitoring->getOwner() !== $this->getUser()) {
            $this->redirectToRoute('app_project_monitoring_index');
        }

        if ($this->isCsrfTokenValid('delete'.$projectMonitoring->getId(), $request->request->get('_token'))) {
            $projectMonitoringRepository->remove($projectMonitoring, true);
        }

        return $this->redirectToRoute('app_project_monitoring_index', [], Response::HTTP_SEE_OTHER);
    }
}
