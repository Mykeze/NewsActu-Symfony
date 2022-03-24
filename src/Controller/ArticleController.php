<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commentary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
###############################################################################################################################################
########################################################## SHOW ARTICLE #######################################################################
###############################################################################################################################################

    /**
    * @Route("/voir/{cat_alias}/{article_alias}_{id}.html", name="show_article", methods={"GET"})
    */
    public function showArticle(Article $article,EntityManagerInterface $entityManager):Response
    {
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['article'=> $article->getId()]);
        
        return $this->render('article/show_article.html.twig', [
            'article'=> $article,
            'commentaries' => $commentaries
        ]);    
    }
    
    /**
    * @Route("/voir/{alias}", name="show_articles_from_categorie", methods={"GET"})
    */
    public function showArticlesFromCategory(Categorie $category, EntityManagerInterface $entityManager):Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy(['category'=> $category->getId(), 'deletedAt' => null]);
        
        return $this->render('article/show_articles_from_categorie.html.twig', [
            'articles'=> $articles,
            'categorie'=> $category,
        ]);
    }
}