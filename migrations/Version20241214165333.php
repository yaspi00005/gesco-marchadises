<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241214165333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE base_colis_details (id INT AUTO_INCREMENT NOT NULL, colis_id INT NOT NULL, type_marchandises LONGTEXT NOT NULL, poids_volume DOUBLE PRECISION NOT NULL, prix_poids_volume INT NOT NULL, montant_total INT NOT NULL, photo VARCHAR(255) NOT NULL, reception TINYINT(1) NOT NULL, numero_colis VARCHAR(255) NOT NULL, INDEX IDX_B36ED7194D268D70 (colis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE base_colis_details ADD CONSTRAINT FK_B36ED7194D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
        $this->addSql('ALTER TABLE base_colis DROP poids_colis, DROP type_marchandises');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis_details DROP FOREIGN KEY FK_B36ED7194D268D70');
        $this->addSql('DROP TABLE base_colis_details');
        $this->addSql('ALTER TABLE base_colis ADD poids_colis INT NOT NULL, ADD type_marchandises VARCHAR(100) NOT NULL');
    }
}
