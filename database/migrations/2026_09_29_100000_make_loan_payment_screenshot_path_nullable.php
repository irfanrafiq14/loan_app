<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->string('screenshot_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('loan_payments')->whereNull('screenshot_path')->update(['screenshot_path' => '']);

        Schema::table('loan_payments', function (Blueprint $table) {
            $table->string('screenshot_path')->nullable(false)->change();
        });
    }
};
