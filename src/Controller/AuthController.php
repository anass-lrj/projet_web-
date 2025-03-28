<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET'])]
    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // Récupérer les données du formulaire
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // Vérifier si l'utilisateur existe
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new Response('Utilisateur non trouvé', 401);
        }

        // Vérifier le mot de passe (si encodé avec bcrypt par exemple)
        if (!$passwordEncoder->isPasswordValid($user, $password)) {
            return new Response('Mot de passe incorrect', 401);
        }

        // Authentification réussie (tu peux rediriger l'utilisateur)
        return new Response('Connexion réussie', 200);
    }
}
