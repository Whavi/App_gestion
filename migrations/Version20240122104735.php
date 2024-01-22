<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240122104735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, fk_id_product_id INT DEFAULT NULL, fk_id_collaborateur_id INT DEFAULT NULL, signature_id VARCHAR(255) DEFAULT NULL, document_id VARCHAR(255) DEFAULT NULL, signer_id VARCHAR(255) DEFAULT NULL, pdf_not_signed VARCHAR(255) DEFAULT NULL, INDEX IDX_603499938444934F (fk_id_product_id), INDEX IDX_603499939DA7BDC5 (fk_id_collaborateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499938444934F FOREIGN KEY (fk_id_product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499939DA7BDC5 FOREIGN KEY (fk_id_collaborateur_id) REFERENCES collaborateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499938444934F');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499939DA7BDC5');
        $this->addSql('DROP TABLE contrat');
    }
}
