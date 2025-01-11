<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250111141658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paiements (id INT AUTO_INCREMENT NOT NULL, colis_id INT NOT NULL, caissier_id INT DEFAULT NULL, montants INT NOT NULL, date_reception DATETIME NOT NULL, mode_paiement VARCHAR(255) NOT NULL, INDEX IDX_E1B02E124D268D70 (colis_id), INDEX IDX_E1B02E12B514973B (caissier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E124D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E12B514973B FOREIGN KEY (caissier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user CHANGE usename usename VARCHAR(8) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E124D268D70');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E12B514973B');
        $this->addSql('DROP TABLE paiements');
        $this->addSql('ALTER TABLE `user` CHANGE usename usename VARCHAR(180) NOT NULL');
    }
}
