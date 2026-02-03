<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {

            if (!Schema::hasColumn('tasks', 'priorite')) {
                $table->enum('priorite', ['Basse', 'Normale', 'Haute'])
                      ->default('Normale')
                      ->after('description');
            }

            if (!Schema::hasColumn('tasks', 'date_fin')) {
                $table->date('date_fin')
                      ->nullable()
                      ->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'priorite')) {
                $table->dropColumn('priorite');
            }

            if (Schema::hasColumn('tasks', 'date_fin')) {
                $table->dropColumn('date_fin');
            }
        });
    }
};
