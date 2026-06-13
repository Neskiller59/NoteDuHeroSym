<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260518103351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competence DROP FOREIGN KEY FK_94D4687F45B0BCD');
        $this->addSql('ALTER TABLE competence CHANGE effet effet LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE competence ADD CONSTRAINT FK_94D4687F45B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competence DROP FOREIGN KEY FK_94D4687F45B0BCD');
        $this->addSql('ALTER TABLE competence CHANGE effet effet VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE competence ADD CONSTRAINT FK_94D4687F45B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
