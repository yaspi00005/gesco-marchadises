<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250906202208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bases_sahel (id INT AUTO_INCREMENT NOT NULL, clients_id INT NOT NULL, sahel INT NOT NULL, date_operations DATE NOT NULL, INDEX IDX_BC5F270AB014612 (clients_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichiers (id INT AUTO_INCREMENT NOT NULL, colis_id INT NOT NULL, date_suppression DATE NOT NULL, statut TINYINT(1) NOT NULL, INDEX IDX_969DB4AB4D268D70 (colis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bases_sahel ADD CONSTRAINT FK_BC5F270AB014612 FOREIGN KEY (clients_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE fichiers ADD CONSTRAINT FK_969DB4AB4D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
      
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bases_sahel DROP FOREIGN KEY FK_BC5F270AB014612');
        $this->addSql('ALTER TABLE fichiers DROP FOREIGN KEY FK_969DB4AB4D268D70');
        $this->addSql('DROP TABLE bases_sahel');
        $this->addSql('DROP TABLE fichiers');
      
    }
}
