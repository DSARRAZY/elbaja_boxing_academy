<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200715101103 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE poster (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, poster_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_start DATE NOT NULL, date_end DATE DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_2FB3D0EE5BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, poster_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, location VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_3BAE0AA75BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, poster_id INT DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, INDEX IDX_70E4FA785BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, poster_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_312B3E165BB66C05 (poster_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE5BB66C05 FOREIGN KEY (poster_id) REFERENCES poster (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA75BB66C05 FOREIGN KEY (poster_id) REFERENCES poster (id)');
        $this->addSql('ALTER TABLE member ADD CONSTRAINT FK_70E4FA785BB66C05 FOREIGN KEY (poster_id) REFERENCES poster (id)');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E165BB66C05 FOREIGN KEY (poster_id) REFERENCES poster (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE5BB66C05');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA75BB66C05');
        $this->addSql('ALTER TABLE member DROP FOREIGN KEY FK_70E4FA785BB66C05');
        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E165BB66C05');
        $this->addSql('DROP TABLE poster');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE partner');
    }
}
