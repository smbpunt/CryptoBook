<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109145035 extends AbstractMigration
{
    private const COLUMNS = ['created_at', 'available_at', 'delivered_at'];

    public function getDescription(): string
    {
        return 'Add missing datetime_immutable type comments on messenger_messages';
    }

    public function up(Schema $schema): void
    {
        $this->commentColumns("'(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->commentColumns('NULL');
    }

    private function commentColumns(string $comment): void
    {
        $statements = implode("\n", array_map(
            static fn (string $column): string => \sprintf('COMMENT ON COLUMN messenger_messages.%s IS %s;', $column, $comment),
            self::COLUMNS,
        ));

        // COMMENT ON has no IF EXISTS: skip when the table is missing.
        $this->addSql(<<<SQL
            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = current_schema() AND table_name = 'messenger_messages') THEN
                    {$statements}
                END IF;
            END $$;
            SQL);
    }
}
