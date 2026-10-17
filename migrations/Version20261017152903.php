<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261017152903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(512) NOT NULL, objective LONGTEXT NOT NULL, duration VARCHAR(20) NOT NULL, position INT NOT NULL, date_archived DATETIME DEFAULT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE activity_exercise (activity_id INT NOT NULL, exercise_id INT NOT NULL, INDEX IDX_ED0651281C06096 (activity_id), INDEX IDX_ED06512E934951A (exercise_id), PRIMARY KEY (activity_id, exercise_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE block (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, duration INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, position INT NOT NULL, date_create DATETIME NOT NULL, date_update DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, target VARCHAR(255) NOT NULL, duration INT NOT NULL, date_archived DATE DEFAULT NULL, date_create DATETIME NOT NULL, date_update DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session_block (session_id INT NOT NULL, block_id INT NOT NULL, INDEX IDX_58662FF4613FECDF (session_id), INDEX IDX_58662FF4E9ED820C (block_id), PRIMARY KEY (session_id, block_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE activity_exercise ADD CONSTRAINT FK_ED0651281C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_exercise ADD CONSTRAINT FK_ED06512E934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_block ADD CONSTRAINT FK_58662FF4613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_block ADD CONSTRAINT FK_58662FF4E9ED820C FOREIGN KEY (block_id) REFERENCES block (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_exercise DROP FOREIGN KEY FK_ED0651281C06096');
        $this->addSql('ALTER TABLE activity_exercise DROP FOREIGN KEY FK_ED06512E934951A');
        $this->addSql('ALTER TABLE session_block DROP FOREIGN KEY FK_58662FF4613FECDF');
        $this->addSql('ALTER TABLE session_block DROP FOREIGN KEY FK_58662FF4E9ED820C');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE activity_exercise');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE session_block');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
