<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327153028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE oauth_connection ADD CONSTRAINT FK_BB31DCDDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth_connection RENAME INDEX idx_7b5e2e5aa76ed395 TO IDX_BB31DCDDA76ED395');
        $this->addSql('ALTER TABLE user CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE lastname lastname VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE oauth_connection DROP FOREIGN KEY FK_BB31DCDDA76ED395');
        $this->addSql('ALTER TABLE oauth_connection RENAME INDEX idx_bb31dcdda76ed395 TO IDX_7B5E2E5AA76ED395');
        $this->addSql('ALTER TABLE user CHANGE firstname firstname VARCHAR(55) NOT NULL, CHANGE lastname lastname VARCHAR(55) NOT NULL');
    }
}
