<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211028191930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE strategy_position (id INT AUTO_INCREMENT NOT NULL, position_id INT NOT NULL, percent DOUBLE PRECISION NOT NULL, price_sold DOUBLE PRECISION NOT NULL, INDEX IDX_7D740B6EDD842E46 (position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE strategy_position ADD CONSTRAINT FK_7D740B6EDD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('ALTER TABLE vente DROP is_strategy');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE strategy_position');
        $this->addSql('ALTER TABLE vente ADD is_strategy TINYINT(1) NOT NULL');
    }
}
