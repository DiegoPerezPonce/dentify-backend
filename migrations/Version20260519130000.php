<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove default_box_id from dentist_weekly_schedule';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('dentist_weekly_schedule');

        foreach ($table->getForeignKeys() as $fk) {
            if (\in_array('default_box_id', $fk->getLocalColumns(), true)) {
                $table->removeForeignKey($fk->getName());
            }
        }

        foreach ($table->getIndexes() as $index) {
            if ($index->getColumns() === ['default_box_id']) {
                $table->dropIndex($index->getName());
            }
        }

        if ($table->hasColumn('default_box_id')) {
            $table->dropColumn('default_box_id');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('dentist_weekly_schedule');
        if (!$table->hasColumn('default_box_id')) {
            $table->addColumn('default_box_id', 'integer', ['notnull' => false]);
            $table->addIndex(['default_box_id'], 'IDX_DWS_BOX');
            $table->addForeignKeyConstraint('box', ['default_box_id'], ['id'], [], 'FK_DWS_BOX');
        }
    }
}
