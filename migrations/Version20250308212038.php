<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250308212038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis DROP poids_volume_total');
        $this->addSql('ALTER TABLE base_colis_details ADD observations LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE base_colis_details ADD CONSTRAINT FK_B36ED7194D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
        $this->addSql('ALTER TABLE base_diffusions ADD CONSTRAINT FK_7454AC3A51D1A1DE FOREIGN KEY (destinateurs_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE base_diffusions ADD CONSTRAINT FK_7454AC3A1D29EEBE FOREIGN KEY (expeditions_id) REFERENCES expeditions (id)');
        $this->addSql('ALTER TABLE om ADD CONSTRAINT FK_90BF78724D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E124D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
        $this->addSql('ALTER TABLE paiements ADD CONSTRAINT FK_E1B02E12B514973B FOREIGN KEY (caissier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE programmes ADD CONSTRAINT FK_3631FC3FAB014612 FOREIGN KEY (clients_id) REFERENCES clients (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis ADD poids_volume_total INT NOT NULL');
        $this->addSql('ALTER TABLE base_colis_details DROP FOREIGN KEY FK_B36ED7194D268D70');
        $this->addSql('ALTER TABLE base_colis_details DROP observations');
        $this->addSql('ALTER TABLE base_diffusions DROP FOREIGN KEY FK_7454AC3A51D1A1DE');
        $this->addSql('ALTER TABLE base_diffusions DROP FOREIGN KEY FK_7454AC3A1D29EEBE');
        $this->addSql('ALTER TABLE om DROP FOREIGN KEY FK_90BF78724D268D70');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E124D268D70');
        $this->addSql('ALTER TABLE paiements DROP FOREIGN KEY FK_E1B02E12B514973B');
        $this->addSql('ALTER TABLE programmes DROP FOREIGN KEY FK_3631FC3FAB014612');
    }
}
