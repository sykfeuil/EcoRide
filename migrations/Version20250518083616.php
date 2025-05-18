<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518083616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion ADD passenger_id INT NOT NULL, ADD travel_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion ADD CONSTRAINT FK_AB02B0274502E565 FOREIGN KEY (passenger_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion ADD CONSTRAINT FK_AB02B027ECAB15B3 FOREIGN KEY (travel_id) REFERENCES travel (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AB02B0274502E565 ON opinion (passenger_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AB02B027ECAB15B3 ON opinion (travel_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B0274502E565
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B027ECAB15B3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AB02B0274502E565 ON opinion
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AB02B027ECAB15B3 ON opinion
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE opinion DROP passenger_id, DROP travel_id
        SQL);
    }
}
