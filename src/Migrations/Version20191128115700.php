<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128115700 extends AbstractMigration
{
    private $table = 'car';

    public function getDescription() : string
    {
        return 'Load test data';
    }

    public function up(Schema $schema) : void
    {
        $dateTime = (new \DateTime())->format('Y-m-d H:i:s');
        $data = file_get_contents(__DIR__ . '/../../data/cars.json');
        $data = json_decode($data, true);
        foreach ($data as $row) {
            unset($row['id']);
            $row['created_at'] = $dateTime;
            $row['updated_at'] = $dateTime;

            $this->connection->insert($this->table, $row);
        }
    }

    public function down(Schema $schema) : void
    {
        $this->connection->executeQuery('DELETE FROM ' . $this->table);
    }
}
