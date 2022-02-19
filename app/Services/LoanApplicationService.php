<?php 

namespace App\Services;

use App\Repositories\LoanApplicationRepository;

class LoanApplicationService 
{
    protected $loanApplicationRepository;


    public function __construct(LoanApplicationRepository $loanApplicationRepository)
    {
        $this->loanApplicationRepository = $loanApplicationRepository;
    }

    public function getAll()
    {
        return $this->loanApplicationRepository->getAll();       
    }

    public function createOne($request, $user)
    {
        return $this->loanApplicationRepository->createOne($request, $user);
    }

    public function showOne($id)
    {
        return $this->loanApplicationRepository->showOne($id);
    }

    public function updateOne($request, $id, $user)
    {
        return $this->loanApplicationRepository->updateOne($request, $id, $user);
    }

    public function approveOrRejectLoanApplication($request, $id, $user)
    {
        return $this->loanApplicationRepository->approveOrRejectLoanApplication($request, $id, $user);
    }
}