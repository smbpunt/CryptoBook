<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110091552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the wallet_snapshot table (hourly wallet value per user)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE wallet_snapshot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE wallet_snapshot (id INT NOT NULL, recorded_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, value_usd DOUBLE PRECISION NOT NULL, owner_id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX wallet_snapshot_owner_recorded_at ON wallet_snapshot (owner_id, recorded_at)');
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
