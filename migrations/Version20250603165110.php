<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250603165110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default categories';
    }

    public function up(Schema $schema): void
    {
        $categories = [
            ['name' => 'Concert', 'description' => 'Événements musicaux avec des artistes en live, allant des concerts intimes aux grands festivals.'],
            ['name' => 'Festival', 'description' => 'Rassemblements culturels et artistiques sur plusieurs jours, combinant musique, arts et divertissements.'],
            ['name' => 'Conférence', 'description' => 'Rencontres professionnelles ou éducatives sur un thème donné, avec des experts et des échanges.'],
            ['name' => 'Sport', 'description' => 'Compétitions et activités sportives organisées, du sport amateur aux événements professionnels.'],
            ['name' => 'Soirée', 'description' => 'Fêtes et rassemblements festifs nocturnes, incluant DJ, ambiance et divertissement.'],
            ['name' => 'Exposition', 'description' => 'Présentations artistiques ou culturelles dans un lieu dédié, mettant en valeur des œuvres ou des collections.'],
            ['name' => 'Atelier', 'description' => 'Sessions de formation ou de création interactives, permettant d\'apprendre et de pratiquer.'],
            ['name' => 'Meetup', 'description' => 'Rencontres informelles entre passionnés d\'un sujet, favorisant le networking et les échanges.'],
            ['name' => 'Projection', 'description' => 'Diffusions de films, documentaires ou courts-métrages, suivies de discussions.'],
            ['name' => 'Gaming', 'description' => 'Événements autour du jeu vidéo, LAN parties et tournois pour les passionnés de gaming.']
        ];

        foreach ($categories as $category) {
            $this->addSql('INSERT INTO category (name, description) VALUES (:name, :description)', $category);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM category');
    }
}
