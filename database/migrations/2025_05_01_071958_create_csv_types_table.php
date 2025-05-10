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
       Schema::create('csv_types', function (Blueprint $table) {
        $table->id();
        $table->foreignId('csv_file_id')->constrained()->onDelete('cascade');
        $table->string('type_name');
        $table->json('columns'); 
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_types');
    }
};
