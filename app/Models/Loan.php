<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['loan_code', 'user_id', 'loan_date', 'due_date', 'status', 'approved_by', 'approved_at', 'notes', 'reject_reason', 'rejected_by', 'rejected_at'])]
class Loan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(\App\Models\LoanItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(\App\Models\LoanReturn::class, 'loan_id');
    }

    public function loanReturns(): HasMany
    {
        return $this->returns();
    }
}
