<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Hourly wallet value of each user.
 */
final class Version20261109221258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the wallet_snapshot table (hourly wallet value of each user)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE wallet_snapshot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE wallet_snapshot (id INT NOT NULL, taken_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, value_usd DOUBLE PRECISION NOT NULL, owner_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX wallet_snapshot_owner_taken_at ON wallet_snapshot (owner_id, taken_at)');
        $this->addSql('CREATE INDEX IDX_D8E8A1417E3C61F9 ON wallet_snapshot (owner_id)');
        $this->addSql('ALTER TABLE wallet_snapshot ADD CONSTRAINT FK_D8E8A1417E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE wallet_snapshot_id_seq CASCADE');
        $this->addSql('ALTER TABLE wallet_snapshot DROP CONSTRAINT FK_D8E8A1417E3C61F9');
        $this->addSql('DROP TABLE wallet_snapshot');
    }
}
