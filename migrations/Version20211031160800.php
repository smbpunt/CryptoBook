<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211031160800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blockchain (id INT AUTO_INCREMENT NOT NULL, coin_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2A493AAA84BBDA7 (coin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dapp (id INT AUTO_INCREMENT NOT NULL, blockchain_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_A32F169598073AE1 (blockchain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE strategy_farming (id INT AUTO_INCREMENT NOT NULL, coin_id INT NOT NULL, dapp_id INT DEFAULT NULL, user_id INT NOT NULL, nb_coins DOUBLE PRECISION NOT NULL, apr DOUBLE PRECISION NOT NULL, INDEX IDX_A59DF77284BBDA7 (coin_id), INDEX IDX_A59DF7722AD03706 (dapp_id), INDEX IDX_A59DF772A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blockchain ADD CONSTRAINT FK_2A493AAA84BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE dapp ADD CONSTRAINT FK_A32F169598073AE1 FOREIGN KEY (blockchain_id) REFERENCES blockchain (id)');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF77284BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF7722AD03706 FOREIGN KEY (dapp_id) REFERENCES dapp (id)');
        $this->addSql('ALTER TABLE strategy_farming ADD CONSTRAINT FK_A59DF772A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cryptocurrency ADD is_stable TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dapp DROP FOREIGN KEY FK_A32F169598073AE1');
        $this->addSql('ALTER TABLE strategy_farming DROP FOREIGN KEY FK_A59DF7722AD03706');
        $this->addSql('DROP TABLE blockchain');
        $this->addSql('DROP TABLE dapp');
        $this->addSql('DROP TABLE strategy_farming');
        $this->addSql('ALTER TABLE cryptocurrency DROP is_stable');
    }
}
