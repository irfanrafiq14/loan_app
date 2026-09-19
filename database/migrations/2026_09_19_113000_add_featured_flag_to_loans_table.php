<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $offers = DB::table('loans')
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('id')
            ->get()
            ->unique(fn ($loan) => $loan->title.'|'.$loan->amount);

        $now = now();

        foreach ($offers as $offer) {
            $exists = DB::table('loans')
                ->where('is_featured', true)
                ->where('title', $offer->title)
                ->where('amount', $offer->amount)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('loans')->insert([
                'user_id' => null,
                'title' => $offer->title,
                'amount' => $offer->amount,
                'minimum_amount' => $offer->minimum_amount,
                'maximum_amount' => $offer->maximum_amount,
                'loan_date' => $offer->loan_date,
                'due_date' => $offer->due_date,
                'description' => $offer->description,
                'payment_instructions' => $offer->payment_instructions,
                'status' => 'approved',
                'is_featured' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('loans')->where('is_featured', true)->whereNull('user_id')->delete();

        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('is_featured');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
