<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentary;
use App\Form\CommentaryFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentaryController extends AbstractController
{
    /**
     *@Route("/ajouter-un-commentaire?article_id={id}", name="add_commentary", methods={"GET|POST"})
     */
    public function addCommentary(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentary = new Commentary();
        $form = $this->createForm(CommentaryFormType::class, $commentary)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() === false) {
            $this->addFlash("warning", 'Votre commentaire est vide');

            return $this->redirectToRoute('show_article', [
                'cat_alias' => $article->getCategory()->getAlias(),
                'article_alias' => $article->getAlias(),
                'id' => $article->getId(),
            ]);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $commentary->setArticle($article);
            $commentary->setCreatedAt(new Datetime);
            $commentary->setUpdatedAt(new Datetime);

            $commentary->setAuthor($this->getUser());

            $entityManager->persist($commentary);
            $entityManager->flush();

            $this->addFlash('success', "Vous avez commenté l'article <strong> " . $article->getTitle() . " </strong> avec succès !");

            return $this->redirectToRoute('show_article', [
                'cat_alias' => $article->getCategory()->getAlias(),
                'article_alias' => $article->getAlias(),
                'id' => $article->getId(),
            ]);
        }


        return $this->render('rendered/form_commentary.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("archiver-mon-commentaire", name="soft_delete_commentary", methods="{GET}")
     */
    public function softDeleteCommentary(Commentary $commentary, EntityManagerInterface $entityManager): Response
    {
        $commentary->setDeletedAt(new DateTime());
        $entityManager->persist($commentary);
        $entityManager->flush();

        $this->addFlash('success', "Votre commentaire est archivé");
        return $this->redirectToRoute('show_article');

    }
}
