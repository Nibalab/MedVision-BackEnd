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
        Schema::table('appointments', function (Blueprint $table) {
            // Check if 'appointment_datetime' exists and drop it safely
            if (Schema::hasColumn('appointments', 'appointment_datetime')) {
                $table->dropColumn('appointment_datetime');
            }

            // Modify the existing 'appointment_date' column to store only the date
            $table->date('appointment_date')->change();

            // Add 'appointment_time' after 'appointment_date' if it doesn't exist
            if (!Schema::hasColumn('appointments', 'appointment_time')) {
                $table->time('appointment_time')->after('appointment_date');
            }

            // Update the 'status' column to include the 'canceled' status
            $table->enum('status', ['pending', 'confirmed', 'completed', 'canceled'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Optional: Roll back the changes (not necessary unless you want to revert the migration)
            $table->dropColumn('appointment_time');
            $table->dateTime('appointment_datetime')->nullable(); // Add 'appointment_datetime' back if needed
        });
    }
};
