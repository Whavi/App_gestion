<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240123081533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499938444934F');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_603499939DA7BDC5');
        $this->addSql('DROP INDEX IDX_603499938444934F ON contrat');
        $this->addSql('DROP INDEX IDX_603499939DA7BDC5 ON contrat');
        $this->addSql('ALTER TABLE contrat DROP fk_id_product_id, DROP fk_id_collaborateur_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contrat ADD fk_id_product_id INT DEFAULT NULL, ADD fk_id_collaborateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499938444934F FOREIGN KEY (fk_id_product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_603499939DA7BDC5 FOREIGN KEY (fk_id_collaborateur_id) REFERENCES collaborateur (id)');
        $this->addSql('CREATE INDEX IDX_603499938444934F ON contrat (fk_id_product_id)');
        $this->addSql('CREATE INDEX IDX_603499939DA7BDC5 ON contrat (fk_id_collaborateur_id)');
    }
}
