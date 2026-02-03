<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('notifications', function (Blueprint $table) {
        // On ajoute le type 'info' par défaut pour éviter les erreurs
        $table->string('type')->default('info')->after('message'); 
    });
}

public function down(): void
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->dropColumn('type');
    });
}
};
