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
    Schema::create('conges', function (Blueprint $table) {
        $table->id();

        // relation avec user (ouvrier)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // infos du congé
        $table->date('date_debut');
        $table->date('date_fin');
        $table->string('motif')->nullable();

        // status du congé
        $table->enum('status', ['en_attente', 'accepte', 'refuse'])
              ->default('en_attente');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
