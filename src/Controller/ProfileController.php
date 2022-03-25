<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
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

        // 2e facon statistique nombre commentaire
        $total = count($commentaries );
        $totalOnline = count($entityManager->getRepository(Commentary::class)->findBy(['deletedAt' => null, 'author' => $this->getUser()]));
        $totalArchived = $total - $totalOnline;

        /* foreach ($commentaries as $commentary) {
            if($commentary->getDeletedAt()===null){
                ++$totalArchived;
            }
        } */

        return $this->render('profile/show_user_commentary.html.twig', [
            'commentaries' => $commentaries,
            'total' => $total,
            'totalArchived' => $totalArchived,
            'totalOnline' => $totalOnline,
        ]);
    }
    
}