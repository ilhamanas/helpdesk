<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfms', function (Blueprint $table) {
            $table->string('rfm_number', 20)->nullable()->unique()->after('id');
        });

        // Backfill tiket lama urut id supaya semuanya punya nomor dokumen.
        $seq = 0;
        DB::table('rfms')->orderBy('id')->pluck('id')->each(function ($id) use (&$seq) {
            $seq++;
            DB::table('rfms')->where('id', $id)->update([
                'rfm_number' => 'RFM/IT/' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('rfms', function (Blueprint $table) {
            $table->dropUnique(['rfm_number']);
            $table->dropColumn('rfm_number');
        });
    }
};
