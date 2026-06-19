<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add comment vote counters';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD positive_votes INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE comment ADD negative_votes INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP positive_votes');
        $this->addSql('ALTER TABLE comment DROP negative_votes');
    }
}
