<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220826110317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_token ADD usertoken_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE refresh_token ADD token TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN refresh_token.usertoken_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F21956D9B18CE FOREIGN KEY (usertoken_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74F21956D9B18CE ON refresh_token (usertoken_id)');
        //$this->addSql('ALTER TABLE "user" ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE refresh_token DROP CONSTRAINT FK_C74F21956D9B18CE');
        $this->addSql('DROP INDEX UNIQ_C74F21956D9B18CE');
        $this->addSql('ALTER TABLE refresh_token DROP usertoken_id');
        $this->addSql('ALTER TABLE refresh_token DROP token');
    }
}
