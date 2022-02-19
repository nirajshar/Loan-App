<?php 

namespace App\Services;

use App\Repositories\LoanRepository;

class LoanService 
{

    protected $loanRepository;

    public function __construct(LoanRepository $loanRepository)
    {
        $this->loanRepository = $loanRepository;
    }

    public function getAll()
    {
        return $this->loanRepository->getAll();       
    }

    public function createOne($loanData, $loan_application)
    {
        return $this->loanRepository->createOne($loanData, $loan_application);
    }

    public function showOne($id)
    {
        return $this->loanRepository->showOne($id);
    }

    public function getLoanForLoanApplicationID($loan_application_id)
    {
        return $this->loanRepository->getLoanForLoanApplicationID($loan_application_id);
    }

    public function updateOne($request, $id)
    {
        return $this->loanRepository->updateOne($request, $id);
    }

    public function getLoanByLoanNoWithUserID($loan_no, $user_id)
    {
        return $this->loanRepository->getLoanByLoanNoWithUserID($loan_no, $user_id);
    }

    public function submitPayment($request, $user)
    {
        return $this->loanRepository->submitPayment($request, $user);
    }

    

}