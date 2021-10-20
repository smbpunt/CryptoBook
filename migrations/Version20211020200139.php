<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211020200139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cryptocurrency (id INT AUTO_INCREMENT NOT NULL, libelle_coingecko VARCHAR(255) NOT NULL, libelle VARCHAR(255) DEFAULT NULL, price_usd DOUBLE PRECISION DEFAULT NULL, price_eur DOUBLE PRECISION DEFAULT NULL, mcap_usd DOUBLE PRECISION DEFAULT NULL, mcap_eur DOUBLE PRECISION DEFAULT NULL, url_img_thumb VARCHAR(255) DEFAULT NULL, url_img_small VARCHAR(255) DEFAULT NULL, url_img_large VARCHAR(255) DEFAULT NULL, symbol VARCHAR(5) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cryptocurrency');
    }
}
