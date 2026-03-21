<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table): void {
            $table->id();
            $table->enum('status', ['Active', 'Inactive', 'Placed'])->default('Active');
            $table->string('image_path')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('nationality');
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->enum('german_level', ['None', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'Native'])->default('None');
            $table->text('certificate')->nullable();
            $table->text('expose_university_degree')->nullable();
            $table->string('anabin_status')->nullable();
            $table->json('additional_languages')->nullable();
            $table->string('occupation');
            $table->string('country_of_origin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
