<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211106014039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE strategy_lp (id INT AUTO_INCREMENT NOT NULL, coin1_id INT NOT NULL, coin2_id INT NOT NULL, dapp_id INT NOT NULL, user_id INT NOT NULL, start_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', price_coin1 DOUBLE PRECISION NOT NULL, price_coin2 DOUBLE PRECISION NOT NULL, nb_coin1 DOUBLE PRECISION NOT NULL, nb_coin2 DOUBLE PRECISION NOT NULL, lp_deposit DOUBLE PRECISION DEFAULT NULL, apr DOUBLE PRECISION NOT NULL, INDEX IDX_B267F90A9ED65065 (coin1_id), INDEX IDX_B267F90A8C63FF8B (coin2_id), INDEX IDX_B267F90A2AD03706 (dapp_id), INDEX IDX_B267F90AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A9ED65065 FOREIGN KEY (coin1_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A8C63FF8B FOREIGN KEY (coin2_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90A2AD03706 FOREIGN KEY (dapp_id) REFERENCES dapp (id)');
        $this->addSql('ALTER TABLE strategy_lp ADD CONSTRAINT FK_B267F90AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D03DC35CF40');
        $this->addSql('DROP INDEX idx_c5d30d03dc35cf40 ON loan');
        $this->addSql('CREATE INDEX IDX_C5D30D0384BBDA7 ON loan (coin_id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D03DC35CF40 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE strategy_lp');
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D0384BBDA7');
        $this->addSql('DROP INDEX idx_c5d30d0384bbda7 ON loan');
        $this->addSql('CREATE INDEX IDX_C5D30D03DC35CF40 ON loan (coin_id)');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D0384BBDA7 FOREIGN KEY (coin_id) REFERENCES cryptocurrency (id)');
    }
}
