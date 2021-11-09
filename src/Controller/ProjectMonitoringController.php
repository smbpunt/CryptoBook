<?php

namespace App\Controller;

use App\Entity\ProjectMonitoring;
use App\Form\ProjectMonitoringType;
use App\Repository\ProjectMonitoringRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/project-monitoring")
 */
class ProjectMonitoringController extends AbstractController
{
    /**
     * @Route("/", name="project_monitoring_index", methods={"GET"})
     */
    public function index(ProjectMonitoringRepository $projectMonitoringRepository): Response
    {
        return $this->render('project_monitoring/index.html.twig', [
            'project_monitorings' => $projectMonitoringRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    /**
     * @Route("/new", name="project_monitoring_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $projectMonitoring = new ProjectMonitoring($this->getUser());
        $form = $this->createForm(ProjectMonitoringType::class, $projectMonitoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($projectMonitoring);
            $entityManager->flush();

            return $this->redirectToRoute('project_monitoring_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project_monitoring/new.html.twig', [
            'project_monitoring' => $projectMonitoring,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="project_monitoring_show", methods={"GET"})
     */
    public function show(ProjectMonitoring $projectMonitoring): Response
    {
        if ($projectMonitoring->getUser() !== $this->getUser()) {
            $this->redirectToRoute('project_monitoring_index');
        }

        return $this->render('project_monitoring/show.html.twig', [
            'project_monitoring' => $projectMonitoring,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="project_monitoring_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ProjectMonitoring $projectMonitoring): Response
    {
        if ($projectMonitoring->getUser() !== $this->getUser()) {
            $this->redirectToRoute('project_monitoring_index');
        }

        $form = $this->createForm(ProjectMonitoringType::class, $projectMonitoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_monitoring_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project_monitoring/edit.html.twig', [
            'project_monitoring' => $projectMonitoring,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="project_monitoring_delete", methods={"POST"})
     */
    public function delete(Request $request, ProjectMonitoring $projectMonitoring): Response
    {
        if ($projectMonitoring->getUser() !== $this->getUser()) {
            $this->redirectToRoute('project_monitoring_index');
        }

        if ($this->isCsrfTokenValid('delete' . $projectMonitoring->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($projectMonitoring);
            $entityManager->flush();
        }

        return $this->redirectToRoute('project_monitoring_index', [], Response::HTTP_SEE_OTHER);
    }
}
