<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511142433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE choice (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, which_poll_id_id INT DEFAULT NULL, INDEX IDX_C1AB5A92ED46F750 (which_poll_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE profil (id INT AUTO_INCREMENT NOT NULL, imageProfil LONGTEXT DEFAULT NULL, bio VARCHAR(255) NOT NULL, user_id_id INT DEFAULT NULL, INDEX IDX_E6D6B2979D86650F (user_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reponses (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, choice_id_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1E512EC69D86650F (user_id_id), UNIQUE INDEX UNIQ_1E512EC6DD76709A (choice_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sondage (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_active TINYINT NOT NULL, question VARCHAR(255) NOT NULL, create_at DATETIME NOT NULL, image_url LONGTEXT NOT NULL, category_name VARCHAR(255) NOT NULL, who_make_it_id_id INT DEFAULT NULL, INDEX IDX_7579C89F2077FE30 (who_make_it_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, role JSON NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92ED46F750 FOREIGN KEY (which_poll_id_id) REFERENCES sondage (id)');
        $this->addSql('ALTER TABLE profil ADD CONSTRAINT FK_E6D6B2979D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC69D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC6DD76709A FOREIGN KEY (choice_id_id) REFERENCES choice (id)');
        $this->addSql('ALTER TABLE sondage ADD CONSTRAINT FK_7579C89F2077FE30 FOREIGN KEY (who_make_it_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A92ED46F750');
        $this->addSql('ALTER TABLE profil DROP FOREIGN KEY FK_E6D6B2979D86650F');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC69D86650F');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC6DD76709A');
        $this->addSql('ALTER TABLE sondage DROP FOREIGN KEY FK_7579C89F2077FE30');
        $this->addSql('DROP TABLE choice');
        $this->addSql('DROP TABLE profil');
        $this->addSql('DROP TABLE reponses');
        $this->addSql('DROP TABLE sondage');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
