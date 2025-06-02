<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Event;
use Faker\Factory;
use App\Service\ImageService;


class AppFixtures extends Fixture
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $categories = $manager->getRepository(Category::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

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
            $event->setPhoto($this->imageService->getImageSrc(null));
            $event->setPrice($faker->randomFloat(2, 0, 100));
            $event->setCategory($categories[array_rand($categories)]);
            $event->setOrganizer($users[array_rand($users)]);
            $event->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($event);
        }

        $manager->flush();
    }
}
