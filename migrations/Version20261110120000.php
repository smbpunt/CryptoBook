<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110120000 extends AbstractMigration
{
    private const string INACTIVE_USERS = 'SELECT id FROM "user" WHERE last_connected_at IS NULL OR last_connected_at < \'2025-01-01\'';

    public function getDescription(): string
    {
        return 'Data cleaning: delete users inactive since 2025-01-01 with all their data, then unused dapps, blockchains and cryptocurrencies';
    }

    public function up(Schema $schema): void
    {
        $inactive = self::INACTIVE_USERS;

        // Children of user-owned rows first (no ON DELETE CASCADE on any foreign key).
        $this->addSql("DELETE FROM vente WHERE position_id IN (SELECT id FROM position WHERE owner_id IN ($inactive))");
        $this->addSql("DELETE FROM strategy_position WHERE position_id IN (SELECT id FROM position WHERE owner_id IN ($inactive))");
        $this->addSql("DELETE FROM coin_percent_dca WHERE strategy_dca_id IN (SELECT id FROM strategy_dca WHERE owner_id IN ($inactive))");

        foreach (['position', 'deposit', 'loan', 'nft', 'project_monitoring', 'strategy_dca', 'strategy_farming', 'strategy_lp', 'wallet_snapshot'] as $table) {
            $this->addSql("DELETE FROM $table WHERE owner_id IN ($inactive)");
        }

        $this->addSql("DELETE FROM reset_password_request WHERE user_id IN ($inactive)");
        $this->addSql("DELETE FROM \"user\" WHERE id IN ($inactive)");

        // Reference data left unused, so that its cryptocurrencies can go too.
        $this->addSql(<<<'SQL'
            DELETE FROM dapp d
            WHERE NOT EXISTS (SELECT 1 FROM loan WHERE dapp_id = d.id)
              AND NOT EXISTS (SELECT 1 FROM strategy_farming WHERE dapp_id = d.id)
              AND NOT EXISTS (SELECT 1 FROM strategy_lp WHERE dapp_id = d.id)
            SQL);
        $this->addSql(<<<'SQL'
            DELETE FROM blockchain b
            WHERE NOT EXISTS (SELECT 1 FROM nft WHERE blockchain_id = b.id)
              AND NOT EXISTS (SELECT 1 FROM dapp WHERE blockchain_id = b.id)
            SQL);

        // Cryptocurrencies nobody uses any more, so the scheduled updates stop fetching them.
        $this->addSql(<<<'SQL'
            DELETE FROM cryptocurrency c
            WHERE NOT EXISTS (SELECT 1 FROM position WHERE coin_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM loan WHERE coin_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM nft WHERE cryptocurrency_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM project_monitoring WHERE coin_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM coin_percent_dca WHERE coin_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM strategy_farming WHERE coin_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM strategy_lp WHERE coin1_id = c.id OR coin2_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM blockchain WHERE coin_id = c.id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Deleted data cannot be restored.');
    }
}
