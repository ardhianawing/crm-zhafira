<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $fillable = [
        'nama_template',
        'isi_template',
        'fase',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fase' => 'integer',
    ];

    /**
     * Scope for active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    /**
     * Render isi template dengan mengganti placeholder untuk lead terkait.
     * Sumber tunggal penggantian placeholder (dipakai komponen, halaman detail, dan service).
     */
    public function renderFor(Lead $lead): string
    {
        return str_replace(
            ['{nama_customer}', '{nama_marketing}'],
            [$lead->nama_customer, auth()->user()?->nama_lengkap ?? ''],
            $this->isi_template
        );
    }

    /**
     * Label fase untuk ditampilkan (null = template umum).
     */
    public function faseLabel(): string
    {
        return $this->fase === null ? 'Umum' : "Fase {$this->fase}";
    }
}
