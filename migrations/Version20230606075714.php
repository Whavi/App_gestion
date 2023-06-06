<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606075714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attribution (id INT AUTO_INCREMENT NOT NULL, id_product_id INT NOT NULL, id_collaborateur_id INT NOT NULL, by_user_id INT NOT NULL, date_attribution DATETIME NOT NULL, date_restitution DATETIME NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C751ED49E00EE68D (id_product_id), INDEX IDX_C751ED494BC2B660 (id_collaborateur_id), INDEX IDX_C751ED49DC9C2434 (by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collaborateur (id INT AUTO_INCREMENT NOT NULL, id_depart_id INT NOT NULL, nom VARCHAR(45) NOT NULL, prenom VARCHAR(45) NOT NULL, email VARCHAR(45) NOT NULL, INDEX IDX_770CBCD32E37426C (id_depart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(45) NOT NULL, nom VARCHAR(45) NOT NULL, category VARCHAR(45) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(45) NOT NULL, prenom VARCHAR(45) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49E00EE68D FOREIGN KEY (id_product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED494BC2B660 FOREIGN KEY (id_collaborateur_id) REFERENCES collaborateur (id)');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49DC9C2434 FOREIGN KEY (by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE collaborateur ADD CONSTRAINT FK_770CBCD32E37426C FOREIGN KEY (id_depart_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_6034999367B3B43D');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9EAE6F2D2');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E949CB4726');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE account');
        $this->addSql('ALTER TABLE departement ADD nom VARCHAR(45) NOT NULL, ADD create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP fk_departement_id, DROP name_departement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, fk_contrat_id INT NOT NULL, name_contrat VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, contrat_sended_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6034999367B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, departement_id_id INT NOT NULL, account_id_id INT NOT NULL, INDEX IDX_1483A5E9EAE6F2D2 (departement_id_id), INDEX IDX_1483A5E949CB4726 (account_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, role INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_6034999367B3B43D FOREIGN KEY (users_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9EAE6F2D2 FOREIGN KEY (departement_id_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E949CB4726 FOREIGN KEY (account_id_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49E00EE68D');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED494BC2B660');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49DC9C2434');
        $this->addSql('ALTER TABLE collaborateur DROP FOREIGN KEY FK_770CBCD32E37426C');
        $this->addSql('DROP TABLE attribution');
        $this->addSql('DROP TABLE collaborateur');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE departement ADD fk_departement_id INT NOT NULL, ADD name_departement VARCHAR(255) NOT NULL, DROP nom, DROP create_at, DROP update_at');
    }
}
