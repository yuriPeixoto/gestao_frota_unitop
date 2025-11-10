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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Adicionando as colunas que estão faltando na tabela activity_logs
            $table->enum('criticality', ['low', 'medium', 'high', 'critical'])->default('medium')->after('user_agent');
            $table->enum('category', ['security', 'financial', 'operational', 'administrative'])->default('operational')->after('criticality');
            $table->text('summary')->nullable()->after('category');
            $table->json('tags')->nullable()->after('summary');
            $table->integer('retention_days')->default(365)->after('tags');
            $table->json('affected_users')->nullable()->after('retention_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn([
                'criticality',
                'category',
                'summary',
                'tags',
                'retention_days',
                'affected_users'
            ]);
        });
    }
};
