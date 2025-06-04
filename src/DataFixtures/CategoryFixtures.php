<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['categories'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = [
            'Concert' => 'Événements musicaux avec des artistes en live, allant des concerts intimes aux grands festivals.',
            'Festival' => 'Rassemblements culturels et artistiques sur plusieurs jours, combinant musique, arts et divertissements.',
            'Conférence' => 'Rencontres professionnelles ou éducatives sur un thème donné, avec des experts et des échanges.',
            'Sport' => 'Compétitions et activités sportives organisées, du sport amateur aux événements professionnels.',
            'Soirée' => 'Fêtes et rassemblements festifs nocturnes, incluant DJ, ambiance et divertissement.',
            'Exposition' => 'Présentations artistiques ou culturelles dans un lieu dédié, mettant en valeur des œuvres ou des collections.',
            'Atelier' => 'Sessions de formation ou de création interactives, permettant d\'apprendre et de pratiquer.',
            'Meetup' => 'Rencontres informelles entre passionnés d\'un sujet, favorisant le networking et les échanges.',
            'Projection' => 'Diffusions de films, documentaires ou courts-métrages, suivies de discussions.',
            'Gaming' => 'Événements autour du jeu vidéo, LAN parties et tournois pour les passionnés de gaming.'
        ];

        foreach ($categories as $name => $baseDescription) {
            $category = new Category();
            $category->setName($name);
            $description = substr($baseDescription . ' ' . $faker->sentence(), 0, 255);
            $category->setDescription($description);

            $manager->persist($category);
        }

        $manager->flush();
    }
}
