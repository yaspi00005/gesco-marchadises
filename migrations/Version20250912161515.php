<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250912161515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bonus_queue');
        $this->addSql('ALTER TABLE bases_sahel ADD colis_id INT NOT NULL');
        $this->addSql('ALTER TABLE bases_sahel ADD CONSTRAINT FK_BC5F2704D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
        $this->addSql('CREATE INDEX IDX_BC5F2704D268D70 ON bases_sahel (colis_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C82E74450FF010 ON clients (telephone)');
      
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bonus_queue (id BIGINT AUTO_INCREMENT NOT NULL, colis_id INT NOT NULL, clients_id INT NOT NULL, bonus INT NOT NULL, run_at DATETIME NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX uq_bonus_queue_colis (colis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE bases_sahel DROP FOREIGN KEY FK_BC5F2704D268D70');
        $this->addSql('DROP INDEX IDX_BC5F2704D268D70 ON bases_sahel');
        $this->addSql('ALTER TABLE bases_sahel DROP colis_id');
        $this->addSql('DROP INDEX UNIQ_C82E74450FF010 ON clients');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E124D268D70');
        $this->addSql('ALTER TABLE paiements CHANGE colis_id colis_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E124D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id) ON DELETE CASCADE');
    }
}
