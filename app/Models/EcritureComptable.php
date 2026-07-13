<?php

namespace App\Models;

use App\Enums\StatutEcriture;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcritureComptable extends Model
{
    protected $table = 'ecritures_comptables';

    protected $fillable = [
        'numero_ecriture',
        'journal_comptable_id',
        'date_ecriture',
        'libelle',
        'reference',
        'statut',
        'source_type',
        'source_id',
        'agence_id',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'date_ecriture' => 'date',
            'statut' => StatutEcriture::class,
            'validated_at' => 'datetime',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalComptable::class, 'journal_comptable_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneEcriture::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lignes->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lignes->sum('credit');
    }

    public function estEquilibree(): bool
    {
        return round($this->total_debit, 2) === round($this->total_credit, 2);
    }

    public static function dejaGenereePour(string $sourceType, int $sourceId): ?self
    {
        return static::where('source_type', $sourceType)->where('source_id', $sourceId)->first();
    }
}
