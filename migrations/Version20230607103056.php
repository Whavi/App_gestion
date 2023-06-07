<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607103056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribution ADD product_id INT NOT NULL');
        $this->addSql('ALTER TABLE attribution ADD CONSTRAINT FK_C751ED494584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_C751ED494584665A ON attribution (product_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADEEB69F7B');
        $this->addSql('DROP INDEX IDX_D34A04ADEEB69F7B ON product');
        $this->addSql('ALTER TABLE product DROP attribution_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribution DROP FOREIGN KEY FK_C751ED494584665A');
        $this->addSql('DROP INDEX IDX_C751ED494584665A ON attribution');
        $this->addSql('ALTER TABLE attribution DROP product_id');
        $this->addSql('ALTER TABLE product ADD attribution_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADEEB69F7B FOREIGN KEY (attribution_id) REFERENCES attribution (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADEEB69F7B ON product (attribution_id)');
    }
}
