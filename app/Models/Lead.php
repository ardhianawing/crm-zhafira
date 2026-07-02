<?php

namespace App\Models;

use App\Enums\StatusProspek;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $attributes = [
        'fase_followup' => 0,
        'status_prospek' => 'New',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            $lead->phone_key = self::normalizePhone($lead->no_hp);
            if (!$lead->tgl_next_followup) {
                $lead->tgl_next_followup = now()->toDateString();
            }
        });

        static::updating(function (Lead $lead) {
            if ($lead->isDirty('no_hp')) {
                $lead->phone_key = self::normalizePhone($lead->no_hp);
            }
            if ($lead->isDirty('assigned_to') && $lead->assigned_to !== null) {
                $lead->tgl_next_followup = now()->toDateString();
            }
        });
    }

    protected $fillable = [
        'nama_customer',
        'no_hp',
        'phone_key',
        'status_prospek',
        'fase_followup',
        'tgl_next_followup',
        'catatan_terakhir',
        'assigned_to',
        'assigned_at',
        'sumber_lead',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'status_prospek' => StatusProspek::class,
            'tgl_next_followup' => 'date',
            'assigned_at' => 'datetime',
            'fase_followup' => 'integer',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LeadHistory::class)->orderBy('created_at', 'desc');
    }

    public function duplicateMatches(): HasMany
    {
        return $this->hasMany(self::class, 'phone_key', 'phone_key');
    }

    // Fungsi Baru untuk Badge Operan
    public function isTransferred(): bool
    {
        // Pakai hasil eager-load (withCount) bila tersedia agar tidak memicu N+1
        if (array_key_exists('transferred_histories_count', $this->attributes)) {
            return (int) $this->attributes['transferred_histories_count'] > 0;
        }

        return $this->histories()->where('action', 'transferred')->exists();
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeTodaysTasks($query)
    {
        return $query->whereNotNull('tgl_next_followup')
            ->whereDate('tgl_next_followup', '<=', now()->toDateString());
    }

    public function scopeActiveFollowUps($query)
    {
        return $query->whereRaw("status_prospek NOT IN ('Deal', 'Tidak Berminat')");
    }

    public function scopeDuplicates($query)
    {
        return $query->whereNotNull('phone_key')
            ->whereIn('phone_key', self::query()
                ->select('phone_key')
                ->whereNotNull('phone_key')
                ->groupBy('phone_key')
                ->havingRaw('COUNT(*) > 1'));
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('tgl_next_followup')
            ->whereDate('tgl_next_followup', '<', now()->toDateString());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_prospek', $status);
    }

    public function getWhatsappUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->no_hp);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return "https://wa.me/{$phone}";
    }

    public function getNormalizedPhoneAttribute(): string
    {
        return self::normalizePhone($this->no_hp);
    }

    public function getDuplicateCountAttribute(): int
    {
        if (array_key_exists('duplicate_matches_count', $this->attributes)) {
            return (int) $this->attributes['duplicate_matches_count'];
        }

        if (!$this->phone_key) {
            return 0;
        }

        return self::query()->where('phone_key', $this->phone_key)->count();
    }

    public static function normalizePhone(?string $value): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $value);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    public function isOverdue(): bool
    {
        return $this->tgl_next_followup && $this->tgl_next_followup->lt(now()->startOfDay());
    }

    public function isDueToday(): bool
    {
        return $this->tgl_next_followup && $this->tgl_next_followup->isToday();
    }
}
