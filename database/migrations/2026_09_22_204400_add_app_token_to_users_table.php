<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('app_token', 32)->nullable()->unique()->after('app_name');
        });

        $used = [];

        foreach (DB::table('users')->where('role', 'customer')->orderBy('id')->get() as $user) {
            $token = strtolower((string) ($user->app_token ?? ''));

            if (strlen($token) <= 6) {
                do {
                    $token = strtolower(Str::random(12));
                } while (isset($used[$token]) || strlen($token) <= 6);
            }

            $used[$token] = true;

            DB::table('users')->where('id', $user->id)->update([
                'app_token' => $token,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('app_token');
        });
    }
};
