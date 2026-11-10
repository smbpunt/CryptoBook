<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110152640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make fiat_currency.rates non-nullable (empty object when unknown)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE fiat_currency SET rates = '{}'::json WHERE rates IS NULL");
        $this->addSql('ALTER TABLE fiat_currency ALTER rates SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiat_currency ALTER rates DROP NOT NULL');
    }
}
