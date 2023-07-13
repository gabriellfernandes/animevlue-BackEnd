<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230713170416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite_anime DROP FOREIGN KEY FK_A58C7266794BBE89');
        $this->addSql('ALTER TABLE favorite_anime DROP FOREIGN KEY FK_A58C7266AA17481D');
        $this->addSql('DROP TABLE favorite_anime');
        $this->addSql('ALTER TABLE favorites ADD anime_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5794BBE89 FOREIGN KEY (anime_id) REFERENCES animes (id)');
        $this->addSql('CREATE INDEX IDX_E46960F5794BBE89 ON favorites (anime_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite_anime (favorite_id INT NOT NULL, anime_id INT NOT NULL, INDEX IDX_A58C7266794BBE89 (anime_id), INDEX IDX_A58C7266AA17481D (favorite_id), PRIMARY KEY(favorite_id, anime_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE favorite_anime ADD CONSTRAINT FK_A58C7266794BBE89 FOREIGN KEY (anime_id) REFERENCES animes (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorite_anime ADD CONSTRAINT FK_A58C7266AA17481D FOREIGN KEY (favorite_id) REFERENCES favorites (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5794BBE89');
        $this->addSql('DROP INDEX IDX_E46960F5794BBE89 ON favorites');
        $this->addSql('ALTER TABLE favorites DROP anime_id');
    }
}
