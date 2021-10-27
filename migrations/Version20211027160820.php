<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211027160820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coin_percent_dca (id INT AUTO_INCREMENT NOT NULL, coin_id INT NOT NULL, strategy_dca_id INT NOT NULL, percent DOUBLE PRECISION NOT NULL, INDEX IDX_74DF18AD84BBDA7 (coin_id), INDEX IDX_74DF18AD2AB6BB9C (strategy_dca_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deposit (id INT AUTO_INCREMENT NOT NULL, exchange_id INT DEFAULT NULL, type_id INT DEFAULT NULL, user_id INT NOT NULL, deposited_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', value_eur DOUBLE PRECISION NOT NULL, INDEX IDX_95DB9D3968AFD1A0 (exchange_id), INDEX IDX_95DB9D39C54C8C93 (type_id), INDEX IDX_95DB9D39A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deposit_type (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exchange (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE strategy_dca (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_672C208EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coin_percent_dca ADD CONSTRAINT FK_74DF18AD84BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE coin_percent_dca ADD CONSTRAINT FK_74DF18AD2AB6BB9C FOREIGN KEY (strategy_dca_id) REFERENCES strategy_dca (id)');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D3968AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchange (id)');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39C54C8C93 FOREIGN KEY (type_id) REFERENCES deposit_type (id)');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strategy_dca ADD CONSTRAINT FK_672C208EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D39C54C8C93');
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D3968AFD1A0');
        $this->addSql('ALTER TABLE coin_percent_dca DROP FOREIGN KEY FK_74DF18AD2AB6BB9C');
        $this->addSql('DROP TABLE coin_percent_dca');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE deposit_type');
        $this->addSql('DROP TABLE exchange');
        $this->addSql('DROP TABLE strategy_dca');
    }
}
