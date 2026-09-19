<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->decimal('minimum_amount', 12, 2)->nullable();
            $table->decimal('maximum_amount', 12, 2)->nullable();
            $table->date('loan_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
