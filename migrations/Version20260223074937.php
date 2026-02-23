<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223074937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Rename existing tables/columns instead of recreating them.
        // This preserves data and avoids index-name conflicts (uniq_identifier_username).
        $this->addSql('ALTER TABLE "user" RENAME TO users');
        $this->addSql('ALTER TABLE user_sections RENAME TO users_sections');
        $this->addSql('ALTER TABLE users_sections RENAME COLUMN user_id TO users_id');

        // Align index and constraint names with Doctrine-generated names.
        $this->addSql('ALTER INDEX IF EXISTS idx_cb1ba798a76ed395 RENAME TO IDX_50D7684267B3B43D');
        $this->addSql('ALTER INDEX IF EXISTS idx_cb1ba798577906e4 RENAME TO IDX_50D76842577906E4');
        $this->addSql('ALTER TABLE users_sections RENAME CONSTRAINT fk_cb1ba798a76ed395 TO FK_50D7684267B3B43D');
        $this->addSql('ALTER TABLE users_sections RENAME CONSTRAINT fk_cb1ba798577906e4 TO FK_50D76842577906E4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users_sections RENAME CONSTRAINT FK_50D7684267B3B43D TO fk_cb1ba798a76ed395');
        $this->addSql('ALTER TABLE users_sections RENAME CONSTRAINT FK_50D76842577906E4 TO fk_cb1ba798577906e4');
        $this->addSql('ALTER INDEX IF EXISTS IDX_50D7684267B3B43D RENAME TO idx_cb1ba798a76ed395');
        $this->addSql('ALTER INDEX IF EXISTS IDX_50D76842577906E4 RENAME TO idx_cb1ba798577906e4');
        $this->addSql('ALTER TABLE users_sections RENAME COLUMN users_id TO user_id');
        $this->addSql('ALTER TABLE users_sections RENAME TO user_sections');
        $this->addSql('ALTER TABLE users RENAME TO "user"');
    }
}
