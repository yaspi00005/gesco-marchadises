<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250912165130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bases_sahel ADD statut TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C82E74450FF010 ON clients (telephone)');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E124D268D70');
        $this->addSql('ALTER TABLE paiements CHANGE colis_id colis_id INT NOT NULL');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E124D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bases_sahel DROP statut');
        $this->addSql('DROP INDEX UNIQ_C82E74450FF010 ON clients');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E124D268D70');
        $this->addSql('ALTER TABLE paiements CHANGE colis_id colis_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E124D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id) ON DELETE CASCADE');
    }
}
