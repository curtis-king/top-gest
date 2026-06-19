<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieDocument extends Model
{
    protected $table = 'categories_documents';

    protected $fillable = ['nom', 'description'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
