<?php 

namespace App\Services;

use App\Repositories\LoanAmortizationRepository;

class LoanAmortizationService 
{

    protected $loanAmortizationRepository;


    public function __construct(LoanAmortizationRepository $loanAmortizationRepository)
    {
        $this->loanAmortizationRepository = $loanAmortizationRepository;
    }

    public function createLoanAmortization($loanAmortizationData, $loan_id)
    {
        return $this->loanAmortizationRepository->createLoanAmortization($loanAmortizationData, $loan_id);
    }

    public function deleteLoanAmortization($loan_id)
    {
        return $this->loanAmortizationRepository->deleteLoanAmortization($loan_id);
    }
    
    public function payLoanAmortizationIfExists($loan_id, $amount_paid)
    {
        return $this->loanAmortizationRepository->payLoanAmortizationIfExists($loan_id, $amount_paid);
    }

}