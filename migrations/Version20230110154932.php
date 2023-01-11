<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230110154932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fiat_currency_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fiat_currency (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, fixer_key VARCHAR(5) NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, symbol VARCHAR(5) NOT NULL, rates JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN fiat_currency.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('INSERT INTO fiat_currency (id, libelle, fixer_key, updated_at, symbol, rates) VALUES(nextval(\'fiat_currency_id_seq\'), \'Euro\', \'EUR\', \'2023-01-11 08:49:54.000\', \'€\', \'{"USD":1.073941,"AUD":1.556436}\'::json);');
        $this->addSql('INSERT INTO fiat_currency (id, libelle, fixer_key, updated_at, symbol, rates) VALUES(nextval(\'fiat_currency_id_seq\'),  \'Dollar\', \'USD\', \'2023-01-11 17:42:33.000\', \'$\', \'{"EUR":0.930795,"AUD":1.450125}\'::json);');
        $this->addSql('INSERT INTO fiat_currency (id, libelle, fixer_key, updated_at, symbol, rates) VALUES(nextval(\'fiat_currency_id_seq\'), \'Dollar Australien\', \'AUD\', \'2023-01-11 17:42:33.000\', \'$A\', \'{"EUR":0.641872,"USD":0.689596}\'::json);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fiat_currency_id_seq CASCADE');
        $this->addSql('DROP TABLE fiat_currency');
    }
}
