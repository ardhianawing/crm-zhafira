<?php

use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leads', 'phone_key')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('phone_key', 20)->nullable()->after('no_hp')->index();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE leads MODIFY status_prospek ENUM(
                    'New', 'Cold', 'Warm', 'Hot', 'Deal', 'Tidak Respon', 'Tidak Berminat'
                ) NOT NULL DEFAULT 'New'"
            );
        }

        Lead::query()->select(['id', 'no_hp'])->chunkById(200, function ($leads) {
            foreach ($leads as $lead) {
                DB::table('leads')
                    ->where('id', $lead->id)
                    ->update(['phone_key' => Lead::normalizePhone($lead->no_hp)]);
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('leads')
                ->whereIn('status_prospek', ['Tidak Respon', 'Tidak Berminat'])
                ->update(['status_prospek' => 'Cold']);

            DB::statement(
                "ALTER TABLE leads MODIFY status_prospek ENUM(
                    'New', 'Cold', 'Warm', 'Hot', 'Deal'
                ) NOT NULL DEFAULT 'New'"
            );
        }

        if (Schema::hasColumn('leads', 'phone_key')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropIndex(['phone_key']);
                $table->dropColumn('phone_key');
            });
        }
    }
};
