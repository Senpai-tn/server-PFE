<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210815223017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD last_name VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD adress VARCHAR(255) NOT NULL, ADD tel VARCHAR(20) NOT NULL, ADD login VARCHAR(255) NOT NULL, ADD password VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD expo_id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP last_name, DROP email, DROP adress, DROP tel, DROP login, DROP password, DROP created_at, DROP deleted_at, DROP expo_id');
    }
}
