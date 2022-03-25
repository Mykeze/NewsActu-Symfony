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
##############################################################################################################################################
########################################################## ADD COMMENTARY #####################################################################
###############################################################################################################################################

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

###############################################################################################################################################
########################################################## SOFT DELETE ########################################################################
###############################################################################################################################################
/* 1ere Facon :
                Inconvenient :  Cest tres verbeux
                                Tous les parametres attendu de la route pour faire un redirectToRoute peuvent ne pas etre accessibles.
                Avantage     :  La redirection est  STATIQUE tous les utilisateur seront redirigé au mm endroit

2eme Facon :
                Inconvenient :  La redirection se fera en fonction de l'url de provenance de la requête, à savoir si vous utilisez cette action à plusieurs endroits différents de votre site, l'utilisateur sera redirigé ailleurs que ce que vous avez décidé.
                Avantage     :  La redirection est  DYNAMIQUE elle changera en fonction de la ^rovenance de la requete
*/

    /**
     * @Route("/archiver-mon-commentaire_{id}", name="soft_delete_commentary", methods={"GET"})
     */
    public function softDeleteCommentary(Commentary $commentary, EntityManagerInterface $entityManager, Request $request): Response
    {
        /* * PARCE QUE nous allons rediriger vers 'show_article' qui attend 3 arguments, nous avons injecté l'objet Request ↑↑↑
        cela ns permettra d'acceder aux superglobal PHP ($_GET & $_SERVER => appelés ds l'ordre : query & server )
        nous allons voir 2 facon de rediriger sur la route souhaité    
        => suite du commentaire ds return
        */
        $commentary->setDeletedAt(new DateTime());

        // ############ 1e Façon ############
        //        dd($request->query->get('article_alias'));

        $entityManager->persist($commentary);
        $entityManager->flush();

        // ############ 2e Façon ############
        //     dd($request->server->get('HTTP_REFERER'));


        $this->addFlash('success', "Votre commentaire est archivé");

        // ################# 1e Façon ####################
        // la construction de l'URL a lieu ds le fichier 'show_article.html.twig sur lattribut HTML 'href' de la balise <a>
        //   => VOIR show_article.html.twig pour la suite de la 1e façon


        // Ici Nous recuperons les valeurs des parametres passes ds l'url $_GET (query)
        //return $this->redirectToRoute('show_article', [
        //    'cat_alias' => $request->query->get('cat_alias'),
        //    'article_alias' => $request->query->get('article_alias'),
        //    'id' => $request->query->get('article_id')
        //]);
        // #######################################################
        

  // ############ 2e Façon ############
  # Pour cette façon, on retire les paramètres de l'URL ds le fichier 'show_article.htmml.twig'
 //   => VOIR show_article.html.twig pour la suite de la 2e façon

//   ici on utilise une clé du $_SERVER(server) qui s'appelle 'HTTP_REFERER'
// cette clé contient l'URL de provenance de la requete
        return $this->redirect($request->server->get('HTTP_REFERER'));
        // #######################################################

    }

###############################################################################################################################################
################################################## RESTORE COMMENTARY #########################################################################
###############################################################################################################################################

    /**
     * @Route("/restaurer-un-commentaire_{id}", name="restore_commentary", methods={"GET"})
     */
    public function restoreCommentary(Article $article, Commentary $commentary, EntityManagerInterface $entityManager): Response
    {
        $dateCreate = $commentary->getCreatedAt();

        // dd($dateCreate);
        $commentary->setDeletedAt(null);

        $entityManager->persist($commentary);
        $entityManager->flush();

        $this->addFlash('success', "Le commentaire du DATE A METTRE de l'article ''". $article->getTitle() ."'' a bien été restaurée");
        return $this->redirectToRoute('show_user_commentary');
    }
}
