<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211028162815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE strategy_dca ADD fiat_to_dca_eur DOUBLE PRECISION NOT NULL, ADD farming_to_dca_usd DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE strategy_dca DROP fiat_to_dca_eur, DROP farming_to_dca_usd');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
