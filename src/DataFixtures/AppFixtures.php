<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Event;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['app'];
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupération des catégories existantes
        $categories = $manager->getRepository(Category::class)->findAll();
        if (empty($categories)) {
            throw new \Exception('Veuillez d\'abord charger les CategoryFixtures');
        }

        // Création des utilisateurs
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setVerified(true);
            $user->setIsOAuth(false);
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();

        // Création des événements
        for ($i = 0; $i < 20; $i++) {
            $event = new Event();
            $startDate = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+6 months'));
            $endDate = $startDate->modify('+1 day');

            $event->setName($faker->sentence(3));
            $event->setDescription($faker->paragraph(2));
            $event->setStartDate($startDate);
            $event->setEndDate($endDate);
            $event->setLocation($faker->address);
            $event->setMaxParticipants($faker->numberBetween(10, 100));
            $event->setIsPaid($faker->boolean);
            $event->setPhoto(null);
            $event->setPrice($faker->randomFloat(2, 0, 100));
            $event->setCategory($categories[array_rand($categories)]);
            $event->setOrganizer($users[array_rand($users)]);
            $event->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($event);
        }

        $manager->flush();
    }
}
