<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'fichier_path',
        'type_fichier',
        'taille',
        'date_document',
        'categorie_document_id',
        'agence_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_document' => 'date',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieDocument::class, 'categorie_document_id');
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTailleFormateeAttribute(): string
    {
        $taille = $this->taille ?? 0;
        if ($taille >= 1048576) return round($taille / 1048576, 1) . ' Mo';
        if ($taille >= 1024) return round($taille / 1024, 1) . ' Ko';
        return $taille . ' o';
    }

    public function getEstPdfAttribute(): bool
    {
        return $this->type_fichier === 'application/pdf';
    }

    public function getEstImageAttribute(): bool
    {
        return str_starts_with($this->type_fichier ?? '', 'image/');
    }
}
