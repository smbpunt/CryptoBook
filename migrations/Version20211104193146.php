<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104193146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE loan (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, coin_id INT NOT NULL, nb_coins DOUBLE PRECISION NOT NULL, INDEX IDX_C5D30D03A76ED395 (user_id), INDEX IDX_C5D30D03DC35CF40 (coin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D03A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D03DC35CF40 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE cryptocurrency CHANGE libelle libelle VARCHAR(255) DEFAULT NULL, CHANGE price_usd price_usd DOUBLE PRECISION DEFAULT NULL, CHANGE price_eur price_eur DOUBLE PRECISION DEFAULT NULL, CHANGE mcap_usd mcap_usd DOUBLE PRECISION DEFAULT NULL, CHANGE mcap_eur mcap_eur DOUBLE PRECISION DEFAULT NULL, CHANGE url_img_thumb url_img_thumb VARCHAR(255) DEFAULT NULL, CHANGE url_img_small url_img_small VARCHAR(255) DEFAULT NULL, CHANGE url_img_large url_img_large VARCHAR(255) DEFAULT NULL, CHANGE symbol symbol VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE deposit CHANGE exchange_id exchange_id INT DEFAULT NULL, CHANGE type_id type_id INT DEFAULT NULL, CHANGE deposited_at deposited_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE position CHANGE opened_at opened_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE strategy_farming CHANGE dapp_id dapp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE loan');
        $this->addSql('ALTER TABLE cryptocurrency CHANGE libelle libelle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE price_usd price_usd DOUBLE PRECISION DEFAULT \'NULL\', CHANGE price_eur price_eur DOUBLE PRECISION DEFAULT \'NULL\', CHANGE mcap_usd mcap_usd DOUBLE PRECISION DEFAULT \'NULL\', CHANGE mcap_eur mcap_eur DOUBLE PRECISION DEFAULT \'NULL\', CHANGE url_img_thumb url_img_thumb VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url_img_small url_img_small VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url_img_large url_img_large VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE symbol symbol VARCHAR(5) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE deposit CHANGE exchange_id exchange_id INT DEFAULT NULL, CHANGE type_id type_id INT DEFAULT NULL, CHANGE deposited_at deposited_at DATE DEFAULT \'NULL\' COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE position CHANGE opened_at opened_at DATE DEFAULT \'NULL\' COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE strategy_farming CHANGE dapp_id dapp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATE DEFAULT \'NULL\' COMMENT \'(DC2Type:date_immutable)\'');
    }
}
