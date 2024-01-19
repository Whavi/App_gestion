<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240119085850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attribution (id INT AUTO_INCREMENT NOT NULL, collaborateur_id INT DEFAULT NULL, by_user_id INT DEFAULT NULL, product_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, date_attribution DATETIME NOT NULL, date_restitution DATETIME NOT NULL, description_product VARCHAR(255) DEFAULT NULL, remarque VARCHAR(255) DEFAULT NULL, INDEX IDX_C751ED49A848E3B1 (collaborateur_id), INDEX IDX_C751ED49DC9C2434 (by_user_id), INDEX IDX_C751ED494584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collaborateur (id INT AUTO_INCREMENT NOT NULL, departement_id INT NOT NULL, nom VARCHAR(45) NOT NULL, prenom VARCHAR(45) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_770CBCD3CCF9E01E (departement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(45) NOT NULL, create_at DATETIME NOT NULL, update_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(45) NOT NULL, nom VARCHAR(45) NOT NULL, category VARCHAR(45) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(45) NOT NULL, prenom VARCHAR(45) NOT NULL, plainpassword VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49A848E3B1 FOREIGN KEY (collaborateur_id) REFERENCES collaborateur (id)');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49DC9C2434 FOREIGN KEY (by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED494584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE collaborateur ADD CONSTRAINT FK_770CBCD3CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49A848E3B1');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49DC9C2434');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED494584665A');
        $this->addSql('ALTER TABLE collaborateur DROP FOREIGN KEY FK_770CBCD3CCF9E01E');
        $this->addSql('DROP TABLE attribution');
        $this->addSql('DROP TABLE collaborateur');
        $this->addSql('DROP TABLE departement');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
