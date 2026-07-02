<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            // null = template umum, 0..3 = template untuk fase follow-up tertentu
            $table->unsignedTinyInteger('fase')->nullable()->after('isi_template');
        });

        // Seed template default per-fase (yang sebelumnya hardcoded di FollowUpService)
        // agar admin bisa mengeditnya. Hanya di-insert bila belum ada template ber-fase.
        $hasFaseTemplates = DB::table('whatsapp_templates')->whereNotNull('fase')->exists();

        if (! $hasFaseTemplates) {
            $now = now();
            $defaults = [
                0 => ['Follow-up Awal (Fase 0)', 'Halo {nama_customer}, saya {nama_marketing} dari Zhafira Villa. Terima kasih telah menghubungi kami. Apakah ada yang bisa saya bantu mengenai villa kami?'],
                1 => ['Follow-up Lanjutan (Fase 1)', 'Halo {nama_customer}, ini follow-up dari Zhafira Villa. Apakah sudah sempat melihat info villa kami? Ada pertanyaan yang bisa saya bantu?'],
                2 => ['Pengingat + Promo (Fase 2)', 'Halo {nama_customer}, sekedar mengingatkan mengenai villa kami. Jika berminat, kami sedang ada promo menarik. Silakan hubungi saya untuk info lebih lanjut.'],
                3 => ['Follow-up Terakhir (Fase 3)', 'Halo {nama_customer}, semoga dalam keadaan baik. Jika masih tertarik dengan villa kami, silakan hubungi saya kapan saja. Terima kasih.'],
            ];

            $baseUrutan = (int) (DB::table('whatsapp_templates')->max('urutan') ?? 0);

            foreach ($defaults as $fase => [$nama, $isi]) {
                DB::table('whatsapp_templates')->insert([
                    'nama_template' => $nama,
                    'isi_template' => $isi,
                    'fase' => $fase,
                    'is_active' => true,
                    'urutan' => $baseUrutan + $fase + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
    }
};
