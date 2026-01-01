<?php

use FF\Database\Migrator;
use FF\Database\Table;
use FF\Database\Column;

return new class
{
    public function up(Migrator $migrator): void
    {
        $migrator->alterTable('posts', function (Table $table) {
            $table->addColumn(
                Column::string('image', 255)->nullable()
            );
        });
    }

    public function down(Migrator $migrator): void
    {
        $migrator->alterTable('posts', function (Table $table) {
            $table->dropColumn('image');
        });
    }
};
