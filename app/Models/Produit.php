<?php

namespace App\Models;

use App\Enums\UniteMesure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'description',
        'unite_mesure',
        'prix_achat',
        'prix_vente',
        'stock_min',
        'categorie_produit_id',
        'employee_id',
    ];

    protected function casts(): array
    {
        return [
            'unite_mesure' => UniteMesure::class,
            'prix_achat' => 'decimal:2',
            'prix_vente' => 'decimal:2',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieProduit::class, 'categorie_produit_id');
    }

    public function gestionnaire(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function getStockTotalAttribute(): int
    {
        return $this->stocks->sum('quantite');
    }

    public function getEnAlerteAttribute(): bool
    {
        return $this->stock_min > 0 && $this->stock_total <= $this->stock_min;
    }
}
