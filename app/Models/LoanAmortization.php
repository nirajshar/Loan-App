<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanAmortization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_id',
        'amount_to_pay',
        'interest_amount_to_pay',
        'principal_amount_to_pay',
        'due_date',
        'amount_paid',
        'paid_date'
    ];
}
