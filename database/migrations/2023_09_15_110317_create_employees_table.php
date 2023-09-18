<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // $table->foreignIdFor(User::class);
            $table->foreignIdFor(Department::class);
            $table->foreignIdFor(Employee::class)->nullable(); // Reporting to
            $table->string('status'); // Active - Resigned - Terminated
            $table->date('joinDate');
            $table->date('lastWorkingDate')->nullable();
            $table->date('residencyExpiryDate');
            $table->integer('initialSalary');
            $table->integer('initialLeaveTakenBalance');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
