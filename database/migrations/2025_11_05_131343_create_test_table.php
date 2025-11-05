<?php

namespace Database\Migrations;

use FF\Framework\Database\Migration;

class CreateTestTable extends Migration
{
    public function up(): void
    {
        $this->create('table_name', function($table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->dropIfExists('table_name');
    }
}