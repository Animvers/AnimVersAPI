<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260814134039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92ED46F750 FOREIGN KEY (which_poll_id_id) REFERENCES sondage (id)');
        $this->addSql('ALTER TABLE profil CHANGE bio bio VARCHAR(255) DEFAULT NULL, CHANGE user_id_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE profil ADD CONSTRAINT FK_E6D6B2979D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC69D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponses ADD CONSTRAINT FK_1E512EC6DD76709A FOREIGN KEY (choice_id_id) REFERENCES choice (id)');
        $this->addSql('ALTER TABLE sondage ADD CONSTRAINT FK_7579C89F2077FE30 FOREIGN KEY (who_make_it_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user DROP token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A92ED46F750');
        $this->addSql('ALTER TABLE profil DROP FOREIGN KEY FK_E6D6B2979D86650F');
        $this->addSql('ALTER TABLE profil CHANGE bio bio VARCHAR(255) NOT NULL, CHANGE user_id_id user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC69D86650F');
        $this->addSql('ALTER TABLE reponses DROP FOREIGN KEY FK_1E512EC6DD76709A');
        $this->addSql('ALTER TABLE sondage DROP FOREIGN KEY FK_7579C89F2077FE30');
        $this->addSql('ALTER TABLE user ADD token VARCHAR(255) NOT NULL');
    }
}
