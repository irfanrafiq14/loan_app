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
        Schema::table('loans', function (Blueprint $table) {
            $table->string('reference_code', 16)->nullable()->unique()->after('title');
            $table->decimal('total_due', 12, 2)->nullable()->after('amount');
        });

        $used = [];

        foreach (DB::table('loans')->orderBy('id')->get() as $loan) {
            $code = strtoupper((string) ($loan->reference_code ?? ''));

            if ($code === '') {
                do {
                    $code = strtoupper(Str::random(8));
                } while (isset($used[$code]));
            }

            $used[$code] = true;

            DB::table('loans')->where('id', $loan->id)->update([
                'reference_code' => $code,
                'total_due' => $loan->total_due ?? $loan->amount,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['reference_code', 'total_due']);
        });
    }
};
