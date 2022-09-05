<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220905152836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product ADD quantity INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD location VARCHAR(255) NOT NULL');
        //$this->addSql('ALTER TABLE "user" ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE product DROP name');
        $this->addSql('ALTER TABLE product DROP quantity');
        $this->addSql('ALTER TABLE product DROP location');
    }
}
