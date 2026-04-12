<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['loan_id', 'user_id', 'return_date', 'status', 'notes'])]
class LoanReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Loan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
