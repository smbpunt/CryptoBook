<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111091521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit RENAME COLUMN value_eur TO amount');
        $this->addSql('ALTER TABLE deposit ADD fiat_currency_id INT');
        $this->addSql('UPDATE deposit SET fiat_currency_id = 1 WHERE fiat_currency_id IS NULL');
        $this->addSql('ALTER TABLE deposit ALTER COLUMN fiat_currency_id SET NOT NULL');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39C4F47010 FOREIGN KEY (fiat_currency_id) REFERENCES fiat_currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_95DB9D39C4F47010 ON deposit (fiat_currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE deposit DROP CONSTRAINT FK_95DB9D39C4F47010');
        $this->addSql('DROP INDEX IDX_95DB9D39C4F47010');
        $this->addSql('ALTER TABLE deposit DROP fiat_currency_id');
        $this->addSql('ALTER TABLE deposit RENAME COLUMN amount TO value_eur');
    }
}
