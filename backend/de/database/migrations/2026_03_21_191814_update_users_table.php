<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['admin', 'client'])->default('client')->after('password');
            $table->string('company_name')->nullable()->after('role');
            $table->string('country')->nullable()->after('company_name');
            $table->boolean('is_active')->default(true)->after('country');
            $table->enum('preferred_language', ['en', 'de'])->default('en')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'company_name', 'country', 'is_active', 'preferred_language']);
        });
    }
};
