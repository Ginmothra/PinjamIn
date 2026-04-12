<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'code', 'name', 'description', 'stock', 'condition', 'is_active'])]
class Item extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(\App\Models\LoanItem::class);
    }
}
