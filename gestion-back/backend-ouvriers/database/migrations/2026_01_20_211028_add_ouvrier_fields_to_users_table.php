<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('ouvrier')->after('password');
            }
            if (!Schema::hasColumn('users', 'telephone')) {
                $table->string('telephone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'poste')) {
                $table->string('poste')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('users', 'date_embauche')) {
                $table->date('date_embauche')->nullable()->after('poste');
            }
            if (!Schema::hasColumn('users', 'adresse')) {
                $table->text('adresse')->nullable()->after('date_embauche');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'telephone')) {
                $table->dropColumn('telephone');
            }
            if (Schema::hasColumn('users', 'poste')) {
                $table->dropColumn('poste');
            }
            if (Schema::hasColumn('users', 'date_embauche')) {
                $table->dropColumn('date_embauche');
            }
            if (Schema::hasColumn('users', 'adresse')) {
                $table->dropColumn('adresse');
            }
        });
    }
};