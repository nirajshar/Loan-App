<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'loan_no',
        'loan_application_id',
        'amount_asked',
        'repayment_period_asked',
        'amount_approved',
        'repayment_period_approved',
        'principal_amount',
        'interest_percentage',
        'interest_amount',
        'loan_status',
        'balance_amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
