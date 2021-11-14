<?php

namespace App\Repositories;

use App\Models\LoanHistory;
use Illuminate\Support\Facades\DB;

class LoanHistoryRepository extends BaseRepository
{
    public function getFieldsSearchable()
    {
        return [];
    }

    public function model()
    {
        return LoanHistory::class;
    }

    public function checkLoanByDay($loanId)
    {
        return $this->model->where('loan_id', $loanId)->whereDate('created_at', date('Y-m-d'))->first();
    }
}
