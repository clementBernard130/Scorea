<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123115044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_classe (user_id INT NOT NULL, classe_id INT NOT NULL, PRIMARY KEY (user_id, classe_id))');
        $this->addSql('CREATE INDEX IDX_EAD5A4ABA76ED395 ON user_classe (user_id)');
        $this->addSql('CREATE INDEX IDX_EAD5A4AB8F5EA509 ON user_classe (classe_id)');
        $this->addSql('ALTER TABLE user_classe ADD CONSTRAINT FK_EAD5A4ABA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_classe ADD CONSTRAINT FK_EAD5A4AB8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE "user" DROP class_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_classe DROP CONSTRAINT FK_EAD5A4ABA76ED395');
        $this->addSql('ALTER TABLE user_classe DROP CONSTRAINT FK_EAD5A4AB8F5EA509');
        $this->addSql('DROP TABLE user_classe');
        $this->addSql('ALTER TABLE "user" ADD class_id INT DEFAULT NULL');
    }
}
