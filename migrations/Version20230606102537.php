<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606102537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(55) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49E00EE68D');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED494BC2B660');
        $this->addSql('DROP INDEX IDX_C751ED49E00EE68D ON attribution');
        $this->addSql('DROP INDEX IDX_C751ED494BC2B660 ON attribution');
        $this->addSql('ALTER TABLE attribution ADD fk_product_id INT NOT NULL, ADD fk_collaborateur_id INT NOT NULL, DROP id_product_id, DROP id_collaborateur_id, CHANGE create_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49B5EAACC9 FOREIGN KEY (fk_product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49FE2284C6 FOREIGN KEY (fk_collaborateur_id) REFERENCES collaborateur (id)');
        $this->addSql('CREATE INDEX IDX_C751ED49B5EAACC9 ON attribution (fk_product_id)');
        $this->addSql('CREATE INDEX IDX_C751ED49FE2284C6 ON attribution (fk_collaborateur_id)');
        $this->addSql('DROP INDEX IDX_770CBCD32E37426C ON collaborateur');
        $this->addSql('ALTER TABLE collaborateur DROP email, CHANGE nom nom VARCHAR(55) NOT NULL, CHANGE prenom prenom VARCHAR(55) NOT NULL, CHANGE id_depart_id fk_depart_id INT NOT NULL');
        $this->addSql('ALTER TABLE collaborateur ADD CONSTRAINT FK_770CBCD3CF5B29DB FOREIGN KEY (fk_depart_id) REFERENCES departement (id)');
        $this->addSql('CREATE INDEX IDX_770CBCD3CF5B29DB ON collaborateur (fk_depart_id)');
        $this->addSql('ALTER TABLE product ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP create_at, DROP update_at, CHANGE identifiant identifiant VARCHAR(55) NOT NULL, CHANGE nom nom VARCHAR(55) NOT NULL, CHANGE category category VARCHAR(55) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE nom nom VARCHAR(55) NOT NULL, CHANGE prenom prenom VARCHAR(55) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collaborateur DROP FOREIGN KEY FK_770CBCD3CF5B29DB');
        $this->addSql('DROP TABLE departement');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX IDX_770CBCD3CF5B29DB ON collaborateur');
        $this->addSql('ALTER TABLE collaborateur ADD email VARCHAR(45) NOT NULL, CHANGE nom nom VARCHAR(45) NOT NULL, CHANGE prenom prenom VARCHAR(45) NOT NULL, CHANGE fk_depart_id id_depart_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_770CBCD32E37426C ON collaborateur (id_depart_id)');
        $this->addSql('ALTER TABLE user CHANGE nom nom VARCHAR(45) NOT NULL, CHANGE prenom prenom VARCHAR(45) NOT NULL');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49B5EAACC9');
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED49FE2284C6');
        $this->addSql('DROP INDEX IDX_C751ED49B5EAACC9 ON attribution');
        $this->addSql('DROP INDEX IDX_C751ED49FE2284C6 ON attribution');
        $this->addSql('ALTER TABLE attribution ADD id_product_id INT NOT NULL, ADD id_collaborateur_id INT NOT NULL, DROP fk_product_id, DROP fk_collaborateur_id, CHANGE created_at create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED49E00EE68D FOREIGN KEY (id_product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED494BC2B660 FOREIGN KEY (id_collaborateur_id) REFERENCES collaborateur (id)');
        $this->addSql('CREATE INDEX IDX_C751ED49E00EE68D ON attribution (id_product_id)');
        $this->addSql('CREATE INDEX IDX_C751ED494BC2B660 ON attribution (id_collaborateur_id)');
        $this->addSql('ALTER TABLE product ADD create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP created_at, DROP updated_at, CHANGE identifiant identifiant VARCHAR(45) NOT NULL, CHANGE nom nom VARCHAR(45) NOT NULL, CHANGE category category VARCHAR(45) NOT NULL');
    }
}
