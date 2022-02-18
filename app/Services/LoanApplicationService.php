<?php 

namespace App\Services;

use App\Repositories\LoanApplicationRepository;
use Exception;
use DB;
use Log;
use Validator;
use App\Models\Loan;

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

    public function createOne($data, $user)
    {
        return $this->loanApplicationRepository->createOne($data, $user);
    }

    public function showOne($id)
    {
        return $this->loanApplicationRepository->showOne($id);
    }

    public function updateOne($data, $id, $user)
    {
        return $this->loanApplicationRepository->updateOne($data, $id, $user);
    }

    public function approveOrRejectLoanApplication($request, $id, $user)
    {
        return $this->loanApplicationRepository->approveOrRejectLoanApplication($request, $id, $user);
    }
}