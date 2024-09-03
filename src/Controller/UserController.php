<?php

namespace App\Controller;

use App\Form\UserPreferencesType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/preferences', name: 'app_user_preferences')]
    public function index(Request $request, UserRepository $repository): Response
    {
        $form = $this->createForm(UserPreferencesType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->add($this->getUser(), true);

            return $this->redirectToRoute('app_user_preferences', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_preferences/index.html.twig', [
            'form' => $form,
        ]);
    }
}
