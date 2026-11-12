<?php

namespace App\User\Controller;

use App\User\Entity\User;
use App\User\Form\UserPreferencesType;
use App\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/preferences', name: 'app_user_preferences')]
    public function index(Request $request, UserRepository $repository, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(UserPreferencesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->add($user, true);

            return $this->redirectToRoute('app_user_preferences', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/preferences.html.twig', [
            'form' => $form,
        ]);
    }
}
