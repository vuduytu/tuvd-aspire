<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanAdminResources;
use App\Repositories\LoanRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LoanController extends Controller
{
    /**
     * @var LoanRepository
     */
    protected $loanRepository;

    /**
     * @param LoanRepository $loanRepository
     */
    public function __construct(LoanRepository $loanRepository)
    {
        $this->loanRepository = $loanRepository;
    }

    public function index(Request $request)
    {
        $paginator = $this->loanRepository->genPaginateChild($request, $this->loanRepository->makeModel());
        return $this->sendSuccess(__('messages.success', ['model' => __('messages.tables.loan')]), LoanAdminResources::collection($paginator['items']) , Arr::except($paginator, ['items']));
    }

    public function show($id)
    {
        $loan = $this->loanRepository->find($id);
        if(!$loan) {
            return $this->sendError(__('messages.not_found',  ['model' => __('messages.tables.loan')]));
        }
        return $this->sendSuccess(__('messages.success',['model' => __('messages.tables.loan')]), new LoanAdminResources($loan));
    }

    public function approve($id)
    {
        $loan = $this->loanRepository->find($id);
        if(!$loan) {
            return $this->sendError(__('messages.not_found',  ['model' => __('messages.tables.loan')]));
        }
        if ($loan->status != PENDING) {
            return $this->sendError('Yêu cầu đang không ở trạng thái pending');
        }
        $loan = $this->loanRepository->update([
            'admin_id' => auth('api')->id(),
            'approve_date' => Carbon::now(),
            'status' => APPROVE
        ], $id);
        return $this->sendSuccess(__('messages.update_success',['model' => __('messages.tables.loan')]), new LoanAdminResources($loan));
    }

    public function reject($id)
    {
        $loan = $this->loanRepository->find($id);
        if(!$loan) {
            return $this->sendError(__('messages.not_found',  ['model' => __('messages.tables.loan')]));
        }
        if ($loan->status != PENDING) {
            return $this->sendError('Yêu cầu đang không ở trạng thái pending');
        }

        $loan = $this->loanRepository->update([
            'admin_id' => auth('api')->id(),
            'approve_date' => Carbon::now(),
            'status' => REJECT
        ], $id);
        return $this->sendSuccess(__('messages.update_success',['model' => __('messages.tables.loan')]), new LoanAdminResources($loan));
    }
}
