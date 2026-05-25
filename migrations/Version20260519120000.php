<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove dentist_id from box (boxes are not owned by a dentist)';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('box');

        foreach ($table->getForeignKeys() as $fk) {
            if (\in_array('dentist_id', $fk->getLocalColumns(), true)) {
                $table->removeForeignKey($fk->getName());
            }
        }

        foreach ($table->getIndexes() as $index) {
            if ($index->getColumns() === ['dentist_id']) {
                $table->dropIndex($index->getName());
            }
        }

        if ($table->hasColumn('dentist_id')) {
            $table->dropColumn('dentist_id');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('box');
        if (!$table->hasColumn('dentist_id')) {
            $table->addColumn('dentist_id', 'integer', ['notnull' => false]);
            $table->addIndex(['dentist_id'], 'IDX_8A9483A1CE0A142');
            $table->addForeignKeyConstraint('dentist', ['dentist_id'], ['id'], [], 'FK_8A9483A1CE0A142');
        }
    }
}
