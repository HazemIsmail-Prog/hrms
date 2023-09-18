<?php

use App\Models\Employee;
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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Employee::class);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type'); // Unpaid - Paid - Sick Leave
            $table->text('notes')->nullable();
            $table->string('status'); // Approved - Pending - Rejected - Over 60 days Adjustment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
