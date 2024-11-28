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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('users');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('ticket_name');
            $table->text('fault_description');
            $table->decimal('cost')->nullable();
            $table->decimal('profit')->nullable();
            $table->decimal('commission_rate')->nullable();
            $table->string('repair_status')->default('PENDING');
            $table->string('payment_status')->default('PENDING');
            $table->string('elapsed_time')->nullable();
            $table->timestamps();
            $table->timestamp('close_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
