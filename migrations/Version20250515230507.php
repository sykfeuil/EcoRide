<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515230507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE opinion (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, mark TINYINT(1) NOT NULL, review LONGTEXT NOT NULL, INDEX IDX_AB02B027C3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion ADD CONSTRAINT FK_AB02B027C3423909 FOREIGN KEY (driver_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel ADD current_state INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B027C3423909
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE opinion
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel DROP current_state
        SQL);
    }
}
