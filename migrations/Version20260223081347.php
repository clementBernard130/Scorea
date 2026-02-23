<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223081347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Rename existing table instead of recreate/drop to preserve data and FKs.
        $this->addSql('ALTER TABLE IF EXISTS skill_unit RENAME TO skill_units');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE IF EXISTS skill_units RENAME TO skill_unit');
    }
}
