<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250111190234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE om (id INT AUTO_INCREMENT NOT NULL, colis_id INT NOT NULL, txnid VARCHAR(255) NOT NULL, pay_token VARCHAR(255) NOT NULL, date_operations DATETIME NOT NULL, order_num VARCHAR(255) NOT NULL, notif TINYINT(1) NOT NULL, statuts VARCHAR(255) NOT NULL, INDEX IDX_90BF78724D268D70 (colis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE om ADD CONSTRAINT FK_90BF78724D268D70 FOREIGN KEY (colis_id) REFERENCES base_colis (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE om DROP FOREIGN KEY FK_90BF78724D268D70');
        $this->addSql('DROP TABLE om');
    }
}
