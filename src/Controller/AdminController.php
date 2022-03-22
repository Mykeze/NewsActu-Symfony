<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

//@IsGranted("ROLE_ADMIN") limite le role a l'admin sur CE controller par sa route
// 3 methode soit directement ds ce fichier a l'annotation soit ds securty.yaml soit par un try catch

/**
* @Route("/admin")
* //IsGranted("ROLE_ADMIN")
*/
class AdminController extends AbstractController
{
    //@IsGranted("ROLE_ADMIN") limite le role a l'admin sur le dashboard controller par sa route
    //danc pas besoin d"tre admin pour ajouter un article

    /**
    * @Route("/tableau-de-bord", name="show_dashboard", methods={"GET"})
    * //IsGranted("ROLE_ADMIN")
    */
    public function showDashboard(EntityManagerInterface $entityManager): Response
    {
        
        ################################### limited access admin ####################################
        /* ICI la 3e methode de restriction admin
         * try/catch fait partie de PHP nativement.
         * Cela a été créé pour gérer les class Exception (erreur).
         * On se sert d'un try/catch lorsqu'on utilise des méthodes (fonctions) QUI LANCE (throw) une Exception.
         * Si la méthode lance l'erreur pendant son exécution, alors l'Excepetion sera 'attrapée' (catch).
         * Le code dans les accolades du catch sera alors exécuté.
         */
        try{
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }
        catch(AccessDeniedException $exception){
            $this->addFlash('warning', 'Acces interdit');
            return $this->redirectToRoute('default_home');
        }
        ################################################################################################

        $articles = $entityManager->getRepository(Article::class)->findAll();//findBy(['deletedAt'=>null]);
        $categories = $entityManager->getRepository(Categorie::class)->findAll();
        $users = $entityManager->getRepository(User::class)->findAll();
     
        
        return $this->render('admin/show_dashboard.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'users' => $users,
        ]);
    }

    /**
    *@Route("/creer-un-article", name="create-article", methods= {"GET|POST"})
    */
    public function createArticle(Request $request,EntityManagerInterface $entityManager,
        SluggerInterface $slugger): Response 
    {
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour accéder à une valeur d'un input de $form, on fait :
            // $form->get('title')->getData()
            $article->setAlias($slugger->Slug($article->getTitle()));
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());

            //Association dun auteur a un article
            $article->setAuthor($this->getUser());

            //variabilisation du fichier 'photo' uploadé.
            $file = $form->get('photo')->getData();

            if ($file) { //equivalent a if(isset($file)===true)si un fichier est uploadé (depuis le formulaire)

                //1ere etape : on deconstruit le nom du fichier et on variablise.
                $extension = '.' . $file->guessExtension();
                //            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                //Assainissement du nom de fichier (du filename)
                // $safeFilename = $slugger->slug($originalFileName);
                $safeFilename = $article->getAlias(); //recupérer le filname en foction du titre de l'article

                //2e etape : on reconstruit le nom du fichier maintenant quil est safe
                //uniqid() est une fonction native php, elle permet d'ajouter une valeur numerique (id)  unique et auto-générée.
                $newFilename = $safeFilename . '.' . uniqid() . $extension; //uniqid("un préfixe",true ou vide si false)

                try {
                    // On a configuré un paramètre 'uploads_dir' dans le fichier services.yaml
                    // Ce param contient le chemin absolu de notre dossier d'upload de photo.

                    $file->move($this->getParameter('uploads_dir'), $newFilename);
                    $article->setPhoto($newFilename);
                } catch (FileException $exception) {
                    //on set le nom de la photo PAS LE CHEMIN
                    /* $article->setPhoto($newFilename); ici avant pourquoi ?*/
                } // END catch
            } //END if($file)

            $entityManager->persist($article);
            $entityManager->flush();

            //ici on ajoute un message qu'on affiche en twig
            $this->addFlash('success', 'Votre article est ligne !');
            return $this->redirectToRoute('show_dashboard');
        } //END if($form)

        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
    *@Route("/modifier-un-article/{id}", name="update_article", methods={"GET|POST"}) //l'action est executé 2 fois et accessible par les deux methods (GET|POST)
    */
    public function updateArticle(Article $article, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        //condition ternaire : $article->getPhoto() ?? '';
        //=> isset($article->getPhoto) ? alors $article->getPhoto sinon ''
        $originalPhoto = $article->getPhoto() ?? '';

        //1er tour en method GET
        $form = $this->createForm(ArticleFormType::class, $article, [
            'photo' => $originalPhoto
        ])->handleRequest($request);

        //2e tour de l'action en method POST quazi idem methode create
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setAlias($slugger->Slug($article->getTitle()));
            $article->setUpdatedAt(new DateTime());
            $file = $form->get('photo')->getData();

            if ($file) {
                $extension = '.' . $file->guessExtension();
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $article->getAlias();
                $newFilename = $safeFilename . '.' . uniqid() . $extension;
                try {
                    $file->move($this->getParameter('uploads_dir'), $newFilename);
                    $article->setPhoto($newFilename);
                } catch (FileException $exception) {
                    //code a executer si erreur aest attrapé
                } //end catch
            } else {
                $article->setPhoto($originalPhoto);
            } //end if file
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', "L'article ''" . $article->getTitle() . "'' a été modifié !");
            return $this->redirectToRoute("show_dashboard");
        } //end if form

        //on retourne la vue pour la method GET
        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }
    /**
    *@Route("/archiver-un-article/{id}", name= "soft_delete_article", methods= {"GET"})
    */
    public function softDeleteArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        # On set la propriété deletedAt pour archiver l'article.
        # De l'autre coté on affichera les articles où deletedAt === null
        $article->setDeletedAt(new DateTime);
        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success', "L'article ''" . $article->getTitle() . "'' a bien été archivé");
        return $this->redirectToRoute('show_dashboard');
    }

    /**
    *@Route("/supprimer-un-article/{id}", name= "hard_delete_article", methods= {"GET"})
    */
    public function hardDeleteArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($article);
        $entityManager->flush($article);

        $this->addFlash('success', "L'article ''" . $article->getTitle() . "'' a été définitvement supprimé");
        return $this->redirectToRoute('show_dashboard');
    }
    /**
    *@Route("/restaurer-un-article/{id}", name= "restore_article", methods= {"GET"})
    */
    public function restoreArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        $article->setDeletedAt();
        $entityManager->persist($article);
        $entityManager->flush($article);
        $this->addFlash('success', "L'article ''" . $article->getTitle() . "'' a été restauré");
        return $this->redirectToRoute('show_dashboard');
    }


}
