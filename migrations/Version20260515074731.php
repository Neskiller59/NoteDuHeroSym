<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515074731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pnj DROP FOREIGN KEY FK_FDA97F2D45B0BCD');
        $this->addSql('ALTER TABLE pnj ADD CONSTRAINT FK_FDA97F2D45B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quest DROP FOREIGN KEY FK_4317F81745B0BCD');
        $this->addSql('ALTER TABLE quest ADD CONSTRAINT FK_4317F81745B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pnj DROP FOREIGN KEY FK_FDA97F2D45B0BCD');
        $this->addSql('ALTER TABLE pnj ADD CONSTRAINT FK_FDA97F2D45B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE quest DROP FOREIGN KEY FK_4317F81745B0BCD');
        $this->addSql('ALTER TABLE quest ADD CONSTRAINT FK_4317F81745B0BCD FOREIGN KEY (hero_id) REFERENCES hero (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
