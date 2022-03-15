<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class DataFixtures extends Fixture
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    //cette fonction load sera executé en ligne de commade avec ; pbc doctrine:fixtures:load --append
    //le drapeau --append permet de ne pas purger la bdd.
    public function load(ObjectManager $manager): void
    { // Déclaration d'une variable de type array, avec le nom des différentes catégories de NewsActu.
        $categories = [
            "Politique",
            "Sport",
            "Sport",
            "Santé",
            "Économie",
            "Informatique",
            "Cinéma",
            "Écologie",
            "Hi Tech"
        ];
        // boucle optimisé pour les array
            //La syntaxe completeds les parentheses est : ($key=>$value)
        foreach ($categories as $cat) {
            //instanciation d'un objet Categorie()
            $categorie = new Categorie();

            //Appel des setters de notre Objet $categorie
            $categorie->setName($cat);
            $categorie->setAlias($this->slugger->slug($cat,));//slug(string $string, string $separator = '-', string $locale = null) slugger pour remplacer des caractere indesirable, ' ', accent etc
            $categorie->setCreatedAt(new DateTime());
            $categorie->setUpdatedAt(new DateTime());

            //EntityManager, on appel sa methode persist pour inserer en bdd l'objet $categorie
            $manager->persist($categorie);
        }
        //on vide l'entity manager
        $manager->flush();
    }
}
