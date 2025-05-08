<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508093201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE travel (id INT AUTO_INCREMENT NOT NULL, driver_id INT NOT NULL, car_id INT NOT NULL, available_seats INT NOT NULL, price DOUBLE PRECISION NOT NULL, starting_date DATETIME NOT NULL, starting_place VARCHAR(255) NOT NULL, ending_date DATETIME NOT NULL, ending_place VARCHAR(255) NOT NULL, INDEX IDX_2D0B6BCEC3423909 (driver_id), INDEX IDX_2D0B6BCEC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE travel_user (travel_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_46CB35E4ECAB15B3 (travel_id), INDEX IDX_46CB35E4A76ED395 (user_id), PRIMARY KEY(travel_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel ADD CONSTRAINT FK_2D0B6BCEC3423909 FOREIGN KEY (driver_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel ADD CONSTRAINT FK_2D0B6BCEC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel_user ADD CONSTRAINT FK_46CB35E4ECAB15B3 FOREIGN KEY (travel_id) REFERENCES travel (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel_user ADD CONSTRAINT FK_46CB35E4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE travel DROP FOREIGN KEY FK_2D0B6BCEC3423909
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel DROP FOREIGN KEY FK_2D0B6BCEC3C6F69F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel_user DROP FOREIGN KEY FK_46CB35E4ECAB15B3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travel_user DROP FOREIGN KEY FK_46CB35E4A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE travel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE travel_user
        SQL);
    }
}
