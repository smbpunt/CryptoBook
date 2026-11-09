<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109164727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store project_monitoring.links as JSON instead of a serialized PHP array (array type removed in DBAL 4)';
    }

    public function up(Schema $schema): void
    {
        foreach ($this->connection->fetchAllKeyValue('SELECT id, links FROM project_monitoring') as $id => $links) {
            $this->addSql('UPDATE project_monitoring SET links = ? WHERE id = ?', [
                json_encode(array_values((array) unserialize($links, ['allowed_classes' => false])), \JSON_THROW_ON_ERROR),
                $id,
            ]);
        }

        $this->addSql('ALTER TABLE project_monitoring ALTER links TYPE JSON USING links::json');
        $this->addSql('COMMENT ON COLUMN project_monitoring.links IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_monitoring ALTER links TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN project_monitoring.links IS \'(DC2Type:array)\'');

        foreach ($this->connection->fetchAllKeyValue('SELECT id, links FROM project_monitoring') as $id => $links) {
            $this->addSql('UPDATE project_monitoring SET links = ? WHERE id = ?', [
                serialize(json_decode($links, true, 512, \JSON_THROW_ON_ERROR)),
                $id,
            ]);
        }
    }
}
