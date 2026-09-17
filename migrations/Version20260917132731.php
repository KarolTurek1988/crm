<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260917132731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, status VARCHAR(30) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact_history (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(30) NOT NULL, subject VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_BD9551CA19EB6921 (client_id), INDEX IDX_BD9551CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lead_import_error (id INT AUTO_INCREMENT NOT NULL, meta_lead_id VARCHAR(255) DEFAULT NULL, error LONGTEXT DEFAULT NULL, payload JSON DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE leads (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, source VARCHAR(50) NOT NULL, meta_lead_id VARCHAR(191) DEFAULT NULL, created_at DATETIME NOT NULL, converted TINYINT NOT NULL, client_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_17904552492E5A6F (meta_lead_id), INDEX IDX_1790455219EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE site_settings (id INT AUTO_INCREMENT NOT NULL, teacher_name VARCHAR(255) NOT NULL, teacher_description LONGTEXT DEFAULT NULL, teacher_photo VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CA19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE leads ADD CONSTRAINT FK_1790455219EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CA19EB6921');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CAA76ED395');
        $this->addSql('ALTER TABLE leads DROP FOREIGN KEY FK_1790455219EB6921');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE contact_history');
        $this->addSql('DROP TABLE lead_import_error');
        $this->addSql('DROP TABLE leads');
        $this->addSql('DROP TABLE site_settings');
        $this->addSql('DROP TABLE user');
    }
}
