<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111015935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loan ADD dapp_id INT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE loan ADD CONSTRAINT FK_C5D30D032AD03706 FOREIGN KEY (dapp_id) REFERENCES dapp (id)');
        $this->addSql('CREATE INDEX IDX_C5D30D032AD03706 ON loan (dapp_id)');
        $this->addSql('ALTER TABLE position ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE strategy_farming ADD entered_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loan DROP FOREIGN KEY FK_C5D30D032AD03706');
        $this->addSql('DROP INDEX IDX_C5D30D032AD03706 ON loan');
        $this->addSql('ALTER TABLE loan DROP dapp_id, DROP description');
        $this->addSql('ALTER TABLE position DROP description');
        $this->addSql('ALTER TABLE strategy_farming DROP entered_at');
    }
}
