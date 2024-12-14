<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241214153652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis CHANGE prix destinateurs_id INT NOT NULL');
        $this->addSql('ALTER TABLE base_colis ADD CONSTRAINT FK_FA204E2B51D1A1DE FOREIGN KEY (destinateurs_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_FA204E2B51D1A1DE ON base_colis (destinateurs_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_colis DROP FOREIGN KEY FK_FA204E2B51D1A1DE');
        $this->addSql('DROP INDEX IDX_FA204E2B51D1A1DE ON base_colis');
        $this->addSql('ALTER TABLE base_colis CHANGE destinateurs_id prix INT NOT NULL');
    }
}
