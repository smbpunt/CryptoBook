<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230111102341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD favorite_fiat_currency_id INT');
        $this->addSql('UPDATE "user" SET favorite_fiat_currency_id = 1 WHERE favorite_fiat_currency_id IS NULL');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN favorite_fiat_currency_id SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64988180915 FOREIGN KEY (favorite_fiat_currency_id) REFERENCES fiat_currency (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D64988180915 ON "user" (favorite_fiat_currency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64988180915');
        $this->addSql('DROP INDEX IDX_8D93D64988180915');
        $this->addSql('ALTER TABLE "user" DROP favorite_fiat_currency_id');
    }
}
