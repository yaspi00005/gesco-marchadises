<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213212138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis ADD expediteurs_id INT NOT NULL, ADD expeditions_id INT DEFAULT NULL, ADD type_expeditions VARCHAR(100) NOT NULL, ADD frais_expeditions INT NOT NULL, ADD paiement VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE base_colis ADD CONSTRAINT FK_FA204E2BDDD4DFB4 FOREIGN KEY (expediteurs_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE base_colis ADD CONSTRAINT FK_FA204E2B1D29EEBE FOREIGN KEY (expeditions_id) REFERENCES expeditions (id)');
        $this->addSql('CREATE INDEX IDX_FA204E2BDDD4DFB4 ON base_colis (expediteurs_id)');
        $this->addSql('CREATE INDEX IDX_FA204E2B1D29EEBE ON base_colis (expeditions_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis DROP FOREIGN KEY FK_FA204E2BDDD4DFB4');
        $this->addSql('ALTER TABLE base_colis DROP FOREIGN KEY FK_FA204E2B1D29EEBE');
        $this->addSql('DROP INDEX IDX_FA204E2BDDD4DFB4 ON base_colis');
        $this->addSql('DROP INDEX IDX_FA204E2B1D29EEBE ON base_colis');
        $this->addSql('ALTER TABLE base_colis DROP expediteurs_id, DROP expeditions_id, DROP type_expeditions, DROP frais_expeditions, DROP paiement');
    }
}
