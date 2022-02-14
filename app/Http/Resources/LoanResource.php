<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'loan_no' => $this->loan_no,
            'amount_asked' => $this->amount_asked,
            'repayment_period_asked' => $this->repayment_period_asked,
            'amount_approved' => $this->amount_approved,
            'repayment_period_approved' => $this->repayment_period_approved,
            'principal_amount' => $this->principal_amount,
            'interest_amount' => $this->interest_amount,
            'loan_status' => $this->loan_status,
            'created_at' => $this->created_at
        ];
    }
}
