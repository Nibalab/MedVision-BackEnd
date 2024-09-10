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
        Schema::table('reports', function (Blueprint $table) {
            // Remove the old columns
            $table->dropColumn('report_content');
            $table->dropColumn('status');
            
            // Add a file_path to store the path to the report document
            $table->string('file_path');  // Store the file path of the uploaded report document
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Re-add the report_content and status columns in the down method
            $table->text('report_content');
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            
            // Drop the file_path column
            $table->dropColumn('file_path');
        });
    }
};
