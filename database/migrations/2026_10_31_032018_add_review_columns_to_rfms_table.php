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
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedTinyInteger('review_rating')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamp('closed_at')->nullable();
        });

        // enum -> string supaya status baru tidak butuh ALTER tiap kali
        DB::statement("ALTER TABLE rfms MODIFY status VARCHAR(20) NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rfms MODIFY status ENUM('open','assigned','in_progress','resolved','closed') NOT NULL DEFAULT 'open'");

        Schema::table('rfms', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approved_by',
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'reviewed_at',
                'review_rating',
                'review_comment',
                'closed_at',
            ]);
        });
    }
};
