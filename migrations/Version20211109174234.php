<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211109174234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE nft (id INT AUTO_INCREMENT NOT NULL, blockchain_id INT NOT NULL, cryptocurrency_id INT NOT NULL, user_id INT NOT NULL, collection VARCHAR(255) NOT NULL, num INT DEFAULT NULL, rank INT DEFAULT NULL, supply INT DEFAULT NULL, price_crypto DOUBLE PRECISION NOT NULL, price_usd DOUBLE PRECISION NOT NULL, purchased_on DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', sold_on DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', price_sold_crypto DOUBLE PRECISION DEFAULT NULL, price_sold_usd DOUBLE PRECISION DEFAULT NULL, percent_sale_fees DOUBLE PRECISION DEFAULT NULL, INDEX IDX_D9C7463C98073AE1 (blockchain_id), INDEX IDX_D9C7463C583FC03A (cryptocurrency_id), INDEX IDX_D9C7463CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_monitoring (id INT AUTO_INCREMENT NOT NULL, coin_id INT DEFAULT NULL, user_id INT NOT NULL, type_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, note LONGTEXT NOT NULL, links LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_A2E9EC6F84BBDA7 (coin_id), INDEX IDX_A2E9EC6FA76ED395 (user_id), INDEX IDX_A2E9EC6FC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_project (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463C98073AE1 FOREIGN KEY (blockchain_id) REFERENCES blockchain (id)');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463C583FC03A FOREIGN KEY (cryptocurrency_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE nft ADD CONSTRAINT FK_D9C7463CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6F84BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_monitoring ADD CONSTRAINT FK_A2E9EC6FC54C8C93 FOREIGN KEY (type_id) REFERENCES type_project (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_monitoring DROP FOREIGN KEY FK_A2E9EC6FC54C8C93');
        $this->addSql('DROP TABLE nft');
        $this->addSql('DROP TABLE project_monitoring');
        $this->addSql('DROP TABLE type_project');
    }
}
