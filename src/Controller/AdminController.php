<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminController extends AbstractController
{
    #[Route("/admin/tableau-de-bord", name: "show_dashboard", methods: ["GET"])]
    public function showDashboard(EntityManagerInterface $entityManager):Response
    {
        $articles=$entityManager->getRepository(Article::class)->findAll();
        return $this->render('admin/show_dashboard.html.twig', [
            'articles' => $articles,
        ]);
        
    }

    #[Route("/admin/creer-un-article", name: "create-article", methods: ["GET|POST"])]
    public function createArticle(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour accéder à une valeur d'un input de $form, on fait :
            // $form->get('title')->getData()
            $article->setAlias($slugger->Slug($article->getTitle()));
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());
            //variabilisation du fichier 'photo' uploadé.
            $file = $form->get('photo')->getData();

            if ($file) { //equivalent a if(isset($file)===true)si un fichier est uploadé (depuis le formulaire)
                
                //1ere etape : on deconstruit le nom du fichier et on variablise.
                $extension = '.' . $file->guessExtension();
                $originalFileName= pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                //Assainissement du nom de fichier (du filename)
                // $safeFilename = $slugger->slug($originalFileName);
                $safeFilename = $article->getAlias();//recupérer le filname en foction du titre de l'article

                //2e etape : on reconstruit le nom du fichier maintenant quil est safe
                //uniqid() est une fonction native php, elle permet d'ajouter une valeur numerique (id)  unique et auto-générée.
                $newFilename=$safeFilename.'.'.uniqid().$extension; //uniqid("un préfixe",true ou vide si false)

                try {
                    // On a configuré un paramètre 'uploads_dir' dans le fichier services.yaml
                        // Ce param contient le chemin absolu de notre dossier d'upload de photo.

                    $file->move($this->getParameter('uploads_dir'), $newFilename);
                } catch (FileException $exception) {
                    //on set le nom de la photo PAS LE CHEMIN
                    $article->setPhoto($newFilename);
                }// END catch
            }//END if($file)

            $entityManager->persist($article);
            $entityManager->flush();

            //ici on ajoute un message qu'on affiche en twig
            $this->addFlash('success','Votre article est ligne !');
            return $this->redirectToRoute('show_dashboard');
        }//END if($form)

        return $this->render('admin/form/create_article.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
