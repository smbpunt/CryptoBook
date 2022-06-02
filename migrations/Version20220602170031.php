<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220602170031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cryptocurrency CHANGE libelle libelle VARCHAR(255) NOT NULL, CHANGE price_usd price_usd DOUBLE PRECISION NOT NULL, CHANGE mcap_usd mcap_usd DOUBLE PRECISION NOT NULL, CHANGE symbol symbol VARCHAR(8) NOT NULL, CHANGE is_stable is_stable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D39A76ED395');
        $this->addSql('ALTER TABLE deposit CHANGE deposited_at deposited_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_95db9d39a76ed395 ON deposit');
        $this->addSql('CREATE INDEX IDX_95DB9D397E3C61F9 ON deposit (owner_id)');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39A76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D03A76ED395');
        $this->addSql('ALTER TABLE loan CHANGE description description LONGTEXT NOT NULL, CHANGE loaned_at loaned_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_c5d30d03a76ed395 ON loan');
        $this->addSql('CREATE INDEX IDX_C5D30D037E3C61F9 ON loan (owner_id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D03A76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE nft DROP FOREIGN KEY FK_D9C7463CA76ED395');
        $this->addSql('ALTER TABLE nft CHANGE purchased_on purchased_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE sold_on sold_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_d9c7463ca76ed395 ON nft');
        $this->addSql('CREATE INDEX IDX_D9C7463C7E3C61F9 ON nft (owner_id)');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463CA76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F5A76ED395');
        $this->addSql('ALTER TABLE position CHANGE opened_at opened_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE description description LONGTEXT NOT NULL, CHANGE is_dca is_dca TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX idx_462ce4f5a76ed395 ON position');
        $this->addSql('CREATE INDEX IDX_462CE4F57E3C61F9 ON position (owner_id)');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5A76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_monitoring DROP FOREIGN KEY FK_A2E9EC6FA76ED395');
        $this->addSql('DROP INDEX idx_a2e9ec6fa76ed395 ON project_monitoring');
        $this->addSql('CREATE INDEX IDX_A2E9EC6F7E3C61F9 ON project_monitoring (owner_id)');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6FA76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strategy_dca DROP FOREIGN KEY FK_672C208EA76ED395');
        $this->addSql('DROP INDEX uniq_672c208ea76ed395 ON strategy_dca');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_672C208E7E3C61F9 ON strategy_dca (owner_id)');
        $this->addSql('ALTER TABLE strategy_dca ADD CONSTRAINT FK_672C208EA76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strategy_farming DROP FOREIGN KEY FK_A59DF772A76ED395');
        $this->addSql('ALTER TABLE strategy_farming CHANGE entered_at entered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('DROP INDEX idx_a59df772a76ed395 ON strategy_farming');
        $this->addSql('CREATE INDEX IDX_A59DF7727E3C61F9 ON strategy_farming (owner_id)');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF772A76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strategy_lp DROP FOREIGN KEY FK_B267F90AA76ED395');
        $this->addSql('ALTER TABLE strategy_lp CHANGE start_at start_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE description description LONGTEXT NOT NULL, CHANGE nb_coin1 nbcoin1 DOUBLE PRECISION NOT NULL');
        $this->addSql('DROP INDEX idx_b267f90aa76ed395 ON strategy_lp');
        $this->addSql('CREATE INDEX IDX_B267F90A7E3C61F9 ON strategy_lp (owner_id)');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90AA76ED395 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD last_connected_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP is_verified');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE cryptocurrency CHANGE libelle libelle VARCHAR(255) DEFAULT NULL, CHANGE price_usd price_usd DOUBLE PRECISION DEFAULT NULL, CHANGE mcap_usd mcap_usd DOUBLE PRECISION DEFAULT NULL, CHANGE symbol symbol VARCHAR(8) DEFAULT NULL, CHANGE is_stable is_stable TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D397E3C61F9');
        $this->addSql('ALTER TABLE deposit CHANGE deposited_at deposited_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('DROP INDEX idx_95db9d397e3c61f9 ON deposit');
        $this->addSql('CREATE INDEX IDX_95DB9D39A76ED395 ON deposit (owner_id)');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D397E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D037E3C61F9');
        $this->addSql('ALTER TABLE loan CHANGE description description LONGTEXT DEFAULT NULL, CHANGE loaned_at loaned_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('DROP INDEX idx_c5d30d037e3c61f9 ON loan');
        $this->addSql('CREATE INDEX IDX_C5D30D03A76ED395 ON loan (owner_id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D037E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE nft DROP FOREIGN KEY FK_D9C7463C7E3C61F9');
        $this->addSql('ALTER TABLE nft CHANGE purchased_on purchased_on DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE sold_on sold_on DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('DROP INDEX idx_d9c7463c7e3c61f9 ON nft');
        $this->addSql('CREATE INDEX IDX_D9C7463CA76ED395 ON nft (owner_id)');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F57E3C61F9');
        $this->addSql('ALTER TABLE position CHANGE opened_at opened_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE description description LONGTEXT DEFAULT NULL, CHANGE is_dca is_dca TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX idx_462ce4f57e3c61f9 ON position');
        $this->addSql('CREATE INDEX IDX_462CE4F5A76ED395 ON position (owner_id)');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F57E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE project_monitoring DROP FOREIGN KEY FK_A2E9EC6F7E3C61F9');
        $this->addSql('DROP INDEX idx_a2e9ec6f7e3c61f9 ON project_monitoring');
        $this->addSql('CREATE INDEX IDX_A2E9EC6FA76ED395 ON project_monitoring (owner_id)');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE strategy_dca DROP FOREIGN KEY FK_672C208E7E3C61F9');
        $this->addSql('DROP INDEX uniq_672c208e7e3c61f9 ON strategy_dca');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_672C208EA76ED395 ON strategy_dca (owner_id)');
        $this->addSql('ALTER TABLE strategy_dca ADD CONSTRAINT FK_672C208E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE strategy_farming DROP FOREIGN KEY FK_A59DF7727E3C61F9');
        $this->addSql('ALTER TABLE strategy_farming CHANGE entered_at entered_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_a59df7727e3c61f9 ON strategy_farming');
        $this->addSql('CREATE INDEX IDX_A59DF772A76ED395 ON strategy_farming (owner_id)');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF7727E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE strategy_lp DROP FOREIGN KEY FK_B267F90A7E3C61F9');
        $this->addSql('ALTER TABLE strategy_lp CHANGE start_at start_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE description description LONGTEXT DEFAULT NULL, CHANGE nbcoin1 nb_coin1 DOUBLE PRECISION NOT NULL');
        $this->addSql('DROP INDEX idx_b267f90a7e3c61f9 ON strategy_lp');
        $this->addSql('CREATE INDEX IDX_B267F90AA76ED395 ON strategy_lp (owner_id)');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD is_verified TINYINT(1) NOT NULL, DROP last_connected_at');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }
}
