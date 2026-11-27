<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261127145618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD visibility VARCHAR(255) NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_AC74095AA76ED395 ON activity (user_id)');
        $this->addSql('ALTER TABLE block ADD visibility VARCHAR(255) NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_831B9722A76ED395 ON block (user_id)');
        $this->addSql('ALTER TABLE exercise ADD visibility VARCHAR(255) NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_AEDAD51CA76ED395 ON exercise (user_id)');
        $this->addSql('ALTER TABLE session ADD visibility VARCHAR(255) NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_D044D5D4A76ED395 ON session (user_id)');
        $this->addSql('ALTER TABLE user ADD active TINYINT NOT NULL, ADD organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64932C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64932C8A3DE ON user (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AA76ED395');
        $this->addSql('DROP INDEX IDX_AC74095AA76ED395 ON activity');
        $this->addSql('ALTER TABLE activity DROP visibility, DROP user_id');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A76ED395');
        $this->addSql('DROP INDEX IDX_831B9722A76ED395 ON block');
        $this->addSql('ALTER TABLE block DROP visibility, DROP user_id');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51CA76ED395');
        $this->addSql('DROP INDEX IDX_AEDAD51CA76ED395 ON exercise');
        $this->addSql('ALTER TABLE exercise DROP visibility, DROP user_id');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('DROP INDEX IDX_D044D5D4A76ED395 ON session');
        $this->addSql('ALTER TABLE session DROP visibility, DROP user_id');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64932C8A3DE');
        $this->addSql('DROP INDEX IDX_8D93D64932C8A3DE ON `user`');
        $this->addSql('ALTER TABLE `user` DROP active, DROP organization_id');
    }
}
