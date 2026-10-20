<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261018121950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE block_activity (block_id INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_F60BF5B5E9ED820C (block_id), INDEX IDX_F60BF5B581C06096 (activity_id), PRIMARY KEY (block_id, activity_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE block_activity ADD CONSTRAINT FK_F60BF5B5E9ED820C FOREIGN KEY (block_id) REFERENCES block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block_activity ADD CONSTRAINT FK_F60BF5B581C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block_activity DROP FOREIGN KEY FK_F60BF5B5E9ED820C');
        $this->addSql('ALTER TABLE block_activity DROP FOREIGN KEY FK_F60BF5B581C06096');
        $this->addSql('DROP TABLE block_activity');
    }
}
