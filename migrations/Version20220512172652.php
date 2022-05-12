<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220512172652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cryptocurrency DROP price_eur, DROP mcap_eur');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CC62CFAD8FE982C4 ON cryptocurrency (libelle_coingecko)');
        $this->addSql('ALTER TABLE loan ADD loaned_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_CC62CFAD8FE982C4 ON cryptocurrency');
        $this->addSql('ALTER TABLE cryptocurrency ADD price_eur DOUBLE PRECISION DEFAULT NULL, ADD mcap_eur DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE loan DROP loaned_at');
    }
}
