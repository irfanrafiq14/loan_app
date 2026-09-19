<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('app_name', 80)->nullable()->after('eligible_offer');
        });

        $latestLinks = DB::table('login_links')
            ->select('user_id', 'app_name')
            ->whereNotNull('app_name')
            ->orderByDesc('id')
            ->get()
            ->unique('user_id');

        foreach ($latestLinks as $link) {
            DB::table('users')
                ->where('id', $link->user_id)
                ->update(['app_name' => $link->app_name]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('app_name');
        });
    }
};
