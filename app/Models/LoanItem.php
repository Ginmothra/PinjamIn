<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['loan_id', 'item_id', 'quantity', 'returned_quantity', 'notes'])]
class LoanItem extends Model
{
    use HasFactory;

    public function loan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Loan::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Item::class);
    }
}
