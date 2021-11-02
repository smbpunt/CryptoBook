<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102160035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cryptocurrency CHANGE libelle libelle VARCHAR(255) DEFAULT NULL, CHANGE price_usd price_usd DOUBLE PRECISION DEFAULT NULL, CHANGE price_eur price_eur DOUBLE PRECISION DEFAULT NULL, CHANGE mcap_usd mcap_usd DOUBLE PRECISION DEFAULT NULL, CHANGE mcap_eur mcap_eur DOUBLE PRECISION DEFAULT NULL, CHANGE url_img_thumb url_img_thumb VARCHAR(255) DEFAULT NULL, CHANGE url_img_small url_img_small VARCHAR(255) DEFAULT NULL, CHANGE url_img_large url_img_large VARCHAR(255) DEFAULT NULL, CHANGE symbol symbol VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE deposit CHANGE exchange_id exchange_id INT DEFAULT NULL, CHANGE type_id type_id INT DEFAULT NULL, CHANGE deposited_at deposited_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE position ADD remaining_coins DOUBLE PRECISION NOT NULL, CHANGE opened_at opened_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE strategy_farming CHANGE dapp_id dapp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('UPDATE position SET remaining_coins = nb_coins');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cryptocurrency CHANGE libelle libelle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE price_usd price_usd DOUBLE PRECISION DEFAULT \'NULL\', CHANGE price_eur price_eur DOUBLE PRECISION DEFAULT \'NULL\', CHANGE mcap_usd mcap_usd DOUBLE PRECISION DEFAULT \'NULL\', CHANGE mcap_eur mcap_eur DOUBLE PRECISION DEFAULT \'NULL\', CHANGE url_img_thumb url_img_thumb VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url_img_small url_img_small VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url_img_large url_img_large VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE symbol symbol VARCHAR(5) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE deposit CHANGE exchange_id exchange_id INT DEFAULT NULL, CHANGE type_id type_id INT DEFAULT NULL, CHANGE deposited_at deposited_at DATE DEFAULT \'NULL\' COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE position DROP remaining_coins, CHANGE opened_at opened_at DATE DEFAULT \'NULL\' COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE strategy_farming CHANGE dapp_id dapp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vente CHANGE sold_at sold_at DATE DEFAULT \'NULL\' COMMENT \'(DC2Type:date_immutable)\'');
    }
}
