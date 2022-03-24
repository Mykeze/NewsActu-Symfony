<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="show_profile", methods={"GET"})
     */
    public function showProfile(): Response
    {
        return $this->render('profile/show_profile.html.twig');
    }

    /**
     * @Route("/profile/tous-mes-commentaires", name="show_user_commentary", methods={"GET"})
     */
    public function showUserCommentary(EntityManagerInterface $entityManager): Response
    {
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['author' => $this->getUser()]);

        return $this->render('profile/show_user_commentary.html.twig', [
            'commentaries' => $commentaries,
        ]);
    }
}