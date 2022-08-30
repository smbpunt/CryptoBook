<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220830143047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE blockchain_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE coin_percent_dca_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cryptocurrency_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dapp_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE deposit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE deposit_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE exchange_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE loan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE nft_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE position_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE project_monitoring_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE strategy_dca_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE strategy_farming_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE strategy_lp_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE strategy_position_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE type_project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE vente_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE blockchain (id INT NOT NULL, coin_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2A493AAA84BBDA7 ON blockchain (coin_id)');
        $this->addSql('CREATE TABLE coin_percent_dca (id INT NOT NULL, coin_id INT NOT NULL, strategy_dca_id INT NOT NULL, percent DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_74DF18AD84BBDA7 ON coin_percent_dca (coin_id)');
        $this->addSql('CREATE INDEX IDX_74DF18AD2AB6BB9C ON coin_percent_dca (strategy_dca_id)');
        $this->addSql('CREATE TABLE cryptocurrency (id INT NOT NULL, libelle_coingecko VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, price_usd DOUBLE PRECISION NOT NULL, mcap_usd DOUBLE PRECISION NOT NULL, url_img_thumb VARCHAR(255) DEFAULT NULL, url_img_small VARCHAR(255) DEFAULT NULL, url_img_large VARCHAR(255) DEFAULT NULL, symbol VARCHAR(8) NOT NULL, color VARCHAR(10) DEFAULT NULL, is_stable BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CC62CFAD8FE982C4 ON cryptocurrency (libelle_coingecko)');
        $this->addSql('CREATE TABLE dapp (id INT NOT NULL, blockchain_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A32F169598073AE1 ON dapp (blockchain_id)');
        $this->addSql('CREATE TABLE deposit (id INT NOT NULL, type_id INT NOT NULL, owner_id INT NOT NULL, exchange_id INT NOT NULL, deposited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, value_eur DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_95DB9D39C54C8C93 ON deposit (type_id)');
        $this->addSql('CREATE INDEX IDX_95DB9D397E3C61F9 ON deposit (owner_id)');
        $this->addSql('CREATE INDEX IDX_95DB9D3968AFD1A0 ON deposit (exchange_id)');
        $this->addSql('COMMENT ON COLUMN deposit.deposited_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE deposit_type (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE exchange (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE loan (id INT NOT NULL, owner_id INT NOT NULL, coin_id INT NOT NULL, dapp_id INT NOT NULL, nb_coins DOUBLE PRECISION NOT NULL, description TEXT NOT NULL, loaned_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C5D30D037E3C61F9 ON loan (owner_id)');
        $this->addSql('CREATE INDEX IDX_C5D30D0384BBDA7 ON loan (coin_id)');
        $this->addSql('CREATE INDEX IDX_C5D30D032AD03706 ON loan (dapp_id)');
        $this->addSql('COMMENT ON COLUMN loan.loaned_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE nft (id INT NOT NULL, blockchain_id INT NOT NULL, cryptocurrency_id INT NOT NULL, owner_id INT NOT NULL, collection VARCHAR(255) NOT NULL, num INT DEFAULT NULL, rank INT DEFAULT NULL, supply INT DEFAULT NULL, price_crypto DOUBLE PRECISION NOT NULL, price_usd DOUBLE PRECISION NOT NULL, price_sold_crypto DOUBLE PRECISION DEFAULT NULL, price_sold_usd DOUBLE PRECISION DEFAULT NULL, percent_sale_fees DOUBLE PRECISION DEFAULT NULL, description TEXT NOT NULL, purchased_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, sold_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D9C7463C98073AE1 ON nft (blockchain_id)');
        $this->addSql('CREATE INDEX IDX_D9C7463C583FC03A ON nft (cryptocurrency_id)');
        $this->addSql('CREATE INDEX IDX_D9C7463C7E3C61F9 ON nft (owner_id)');
        $this->addSql('COMMENT ON COLUMN nft.purchased_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN nft.sold_on IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE position (id INT NOT NULL, coin_id INT NOT NULL, owner_id INT NOT NULL, nb_coins DOUBLE PRECISION NOT NULL, is_opened BOOLEAN NOT NULL, entry_cost DOUBLE PRECISION NOT NULL, opened_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, remaining_coins DOUBLE PRECISION NOT NULL, description TEXT NOT NULL, is_dca BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_462CE4F584BBDA7 ON position (coin_id)');
        $this->addSql('CREATE INDEX IDX_462CE4F57E3C61F9 ON position (owner_id)');
        $this->addSql('COMMENT ON COLUMN position.opened_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE project_monitoring (id INT NOT NULL, coin_id INT DEFAULT NULL, owner_id INT NOT NULL, type_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, description TEXT NOT NULL, note TEXT NOT NULL, links TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A2E9EC6F84BBDA7 ON project_monitoring (coin_id)');
        $this->addSql('CREATE INDEX IDX_A2E9EC6F7E3C61F9 ON project_monitoring (owner_id)');
        $this->addSql('CREATE INDEX IDX_A2E9EC6FC54C8C93 ON project_monitoring (type_id)');
        $this->addSql('COMMENT ON COLUMN project_monitoring.links IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE strategy_dca (id INT NOT NULL, owner_id INT NOT NULL, fiat_to_dca_eur DOUBLE PRECISION NOT NULL, farming_to_dca_usd DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_672C208E7E3C61F9 ON strategy_dca (owner_id)');
        $this->addSql('CREATE TABLE strategy_farming (id INT NOT NULL, coin_id INT NOT NULL, dapp_id INT DEFAULT NULL, owner_id INT NOT NULL, nb_coins DOUBLE PRECISION NOT NULL, apr DOUBLE PRECISION NOT NULL, entered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A59DF77284BBDA7 ON strategy_farming (coin_id)');
        $this->addSql('CREATE INDEX IDX_A59DF7722AD03706 ON strategy_farming (dapp_id)');
        $this->addSql('CREATE INDEX IDX_A59DF7727E3C61F9 ON strategy_farming (owner_id)');
        $this->addSql('COMMENT ON COLUMN strategy_farming.entered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE strategy_lp (id INT NOT NULL, coin1_id INT NOT NULL, coin2_id INT NOT NULL, dapp_id INT NOT NULL, owner_id INT NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, price_coin1 DOUBLE PRECISION NOT NULL, price_coin2 DOUBLE PRECISION NOT NULL, nb_coin1 DOUBLE PRECISION NOT NULL, nb_coin2 DOUBLE PRECISION NOT NULL, lp_deposit DOUBLE PRECISION DEFAULT NULL, apr DOUBLE PRECISION NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B267F90A9ED65065 ON strategy_lp (coin1_id)');
        $this->addSql('CREATE INDEX IDX_B267F90A8C63FF8B ON strategy_lp (coin2_id)');
        $this->addSql('CREATE INDEX IDX_B267F90A2AD03706 ON strategy_lp (dapp_id)');
        $this->addSql('CREATE INDEX IDX_B267F90A7E3C61F9 ON strategy_lp (owner_id)');
        $this->addSql('COMMENT ON COLUMN strategy_lp.start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE strategy_position (id INT NOT NULL, position_id INT NOT NULL, percent DOUBLE PRECISION NOT NULL, price_sold DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D740B6EDD842E46 ON strategy_position (position_id)');
        $this->addSql('CREATE TABLE type_project (id INT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, last_connected_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".last_connected_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE vente (id INT NOT NULL, position_id INT NOT NULL, sold_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, percent DOUBLE PRECISION NOT NULL, price_sold DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_888A2A4CDD842E46 ON vente (position_id)');
        $this->addSql('COMMENT ON COLUMN vente.sold_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE blockchain ADD CONSTRAINT FK_2A493AAA84BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE coin_percent_dca ADD CONSTRAINT FK_74DF18AD84BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE coin_percent_dca ADD CONSTRAINT FK_74DF18AD2AB6BB9C FOREIGN KEY (strategy_dca_id) REFERENCES strategy_dca (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dapp ADD CONSTRAINT FK_A32F169598073AE1 FOREIGN KEY (blockchain_id) REFERENCES blockchain (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39C54C8C93 FOREIGN KEY (type_id) REFERENCES deposit_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D397E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D3968AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchange (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D037E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D0384BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D032AD03706 FOREIGN KEY (dapp_id) REFERENCES dapp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463C98073AE1 FOREIGN KEY (blockchain_id) REFERENCES blockchain (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463C583FC03A FOREIGN KEY (cryptocurrency_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F584BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F57E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6F84BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6FC54C8C93 FOREIGN KEY (type_id) REFERENCES type_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_dca ADD CONSTRAINT FK_672C208E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF77284BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF7722AD03706 FOREIGN KEY (dapp_id) REFERENCES dapp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF7727E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A9ED65065 FOREIGN KEY (coin1_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A8C63FF8B FOREIGN KEY (coin2_id) REFERENCES cryptocurrency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A2AD03706 FOREIGN KEY (dapp_id) REFERENCES dapp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE strategy_position ADD CONSTRAINT FK_7D740B6EDD842E46 FOREIGN KEY (position_id) REFERENCES position (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4CDD842E46 FOREIGN KEY (position_id) REFERENCES position (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE blockchain_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE coin_percent_dca_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cryptocurrency_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE dapp_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE deposit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE deposit_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE exchange_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE loan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE nft_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE position_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE project_monitoring_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE strategy_dca_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE strategy_farming_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE strategy_lp_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE strategy_position_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE type_project_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE vente_id_seq CASCADE');
        $this->addSql('ALTER TABLE blockchain DROP CONSTRAINT FK_2A493AAA84BBDA7');
        $this->addSql('ALTER TABLE coin_percent_dca DROP CONSTRAINT FK_74DF18AD84BBDA7');
        $this->addSql('ALTER TABLE coin_percent_dca DROP CONSTRAINT FK_74DF18AD2AB6BB9C');
        $this->addSql('ALTER TABLE dapp DROP CONSTRAINT FK_A32F169598073AE1');
        $this->addSql('ALTER TABLE deposit DROP CONSTRAINT FK_95DB9D39C54C8C93');
        $this->addSql('ALTER TABLE deposit DROP CONSTRAINT FK_95DB9D397E3C61F9');
        $this->addSql('ALTER TABLE deposit DROP CONSTRAINT FK_95DB9D3968AFD1A0');
        $this->addSql('ALTER TABLE loan DROP CONSTRAINT FK_C5D30D037E3C61F9');
        $this->addSql('ALTER TABLE loan DROP CONSTRAINT FK_C5D30D0384BBDA7');
        $this->addSql('ALTER TABLE loan DROP CONSTRAINT FK_C5D30D032AD03706');
        $this->addSql('ALTER TABLE nft DROP CONSTRAINT FK_D9C7463C98073AE1');
        $this->addSql('ALTER TABLE nft DROP CONSTRAINT FK_D9C7463C583FC03A');
        $this->addSql('ALTER TABLE nft DROP CONSTRAINT FK_D9C7463C7E3C61F9');
        $this->addSql('ALTER TABLE position DROP CONSTRAINT FK_462CE4F584BBDA7');
        $this->addSql('ALTER TABLE position DROP CONSTRAINT FK_462CE4F57E3C61F9');
        $this->addSql('ALTER TABLE project_monitoring DROP CONSTRAINT FK_A2E9EC6F84BBDA7');
        $this->addSql('ALTER TABLE project_monitoring DROP CONSTRAINT FK_A2E9EC6F7E3C61F9');
        $this->addSql('ALTER TABLE project_monitoring DROP CONSTRAINT FK_A2E9EC6FC54C8C93');
        $this->addSql('ALTER TABLE strategy_dca DROP CONSTRAINT FK_672C208E7E3C61F9');
        $this->addSql('ALTER TABLE strategy_farming DROP CONSTRAINT FK_A59DF77284BBDA7');
        $this->addSql('ALTER TABLE strategy_farming DROP CONSTRAINT FK_A59DF7722AD03706');
        $this->addSql('ALTER TABLE strategy_farming DROP CONSTRAINT FK_A59DF7727E3C61F9');
        $this->addSql('ALTER TABLE strategy_lp DROP CONSTRAINT FK_B267F90A9ED65065');
        $this->addSql('ALTER TABLE strategy_lp DROP CONSTRAINT FK_B267F90A8C63FF8B');
        $this->addSql('ALTER TABLE strategy_lp DROP CONSTRAINT FK_B267F90A2AD03706');
        $this->addSql('ALTER TABLE strategy_lp DROP CONSTRAINT FK_B267F90A7E3C61F9');
        $this->addSql('ALTER TABLE strategy_position DROP CONSTRAINT FK_7D740B6EDD842E46');
        $this->addSql('ALTER TABLE vente DROP CONSTRAINT FK_888A2A4CDD842E46');
        $this->addSql('DROP TABLE blockchain');
        $this->addSql('DROP TABLE coin_percent_dca');
        $this->addSql('DROP TABLE cryptocurrency');
        $this->addSql('DROP TABLE dapp');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE deposit_type');
        $this->addSql('DROP TABLE exchange');
        $this->addSql('DROP TABLE loan');
        $this->addSql('DROP TABLE nft');
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP TABLE project_monitoring');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE strategy_dca');
        $this->addSql('DROP TABLE strategy_farming');
        $this->addSql('DROP TABLE strategy_lp');
        $this->addSql('DROP TABLE strategy_position');
        $this->addSql('DROP TABLE type_project');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE vente');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
