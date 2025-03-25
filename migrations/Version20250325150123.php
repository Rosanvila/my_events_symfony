<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325150123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create oauth_connection table and migrate existing OAuth data';
    }

    public function up(Schema $schema): void
    {
        // Création de la table oauth_connection
        $this->addSql('CREATE TABLE oauth_connection (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            provider VARCHAR(50) NOT NULL,
            provider_id VARCHAR(255) NOT NULL,
            email VARCHAR(180) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_7B5E2E5AA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Migration des données Google existantes
        $this->addSql('INSERT INTO oauth_connection (user_id, provider, provider_id, email, created_at)
            SELECT id, "google", google_id, email, NOW()
            FROM user
            WHERE google_id IS NOT NULL');

        // Migration des données Facebook existantes
        $this->addSql('INSERT INTO oauth_connection (user_id, provider, provider_id, email, created_at)
            SELECT id, "facebook", facebook_id, email, NOW()
            FROM user
            WHERE facebook_id IS NOT NULL');

        // Suppression des anciennes colonnes
        $this->addSql('ALTER TABLE user DROP google_id, DROP facebook_id');
    }

    public function down(Schema $schema): void
    {
        // Restauration des colonnes
        $this->addSql('ALTER TABLE user ADD google_id VARCHAR(255) DEFAULT NULL, ADD facebook_id VARCHAR(255) DEFAULT NULL');

        // Restauration des données Google
        $this->addSql('UPDATE user u
            JOIN oauth_connection oc ON u.id = oc.user_id
            SET u.google_id = oc.provider_id
            WHERE oc.provider = "google"');

        // Restauration des données Facebook
        $this->addSql('UPDATE user u
            JOIN oauth_connection oc ON u.id = oc.user_id
            SET u.facebook_id = oc.provider_id
            WHERE oc.provider = "facebook"');

        // Suppression de la table oauth_connection
        $this->addSql('DROP TABLE oauth_connection');
    }
}
