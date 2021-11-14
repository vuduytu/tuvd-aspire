<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanAdminResources;
use App\Http\Resources\LoanClientResources;
use App\Repositories\LoanHistoryRepository;
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
     * @var LoanHistoryRepository
     */
    protected $loanHistoryRepository;

    /**
     * @param LoanRepository $loanRepository
     * @param LoanHistoryRepository $loanHistoryRepository
     */
    public function __construct(LoanRepository $loanRepository, LoanHistoryRepository $loanHistoryRepository)
    {
        $this->loanRepository = $loanRepository;
        $this->loanHistoryRepository = $loanHistoryRepository;
    }

    public function index(Request $request)
    {
        $paginator = $this->loanRepository->genPaginateChildClient($request, $this->loanRepository->makeModel());
        return $this->sendSuccess(__('messages.success', ['model' => __('messages.tables.loan')]), LoanAdminResources::collection($paginator['items']) , Arr::except($paginator, ['items']));
    }

    public function show($id)
    {
        $loan = $this->loanRepository->find($id);
        if(!$loan || $loan->user_id != auth(GUARD_CUSTOMER)->id()) {
            return $this->sendError(__('messages.not_found',  ['model' => __('messages.tables.loan')]));
        }
        return $this->sendSuccess(__('messages.success',['model' => __('messages.tables.loan')]), new LoanClientResources($loan));
    }

    public function create(Request $request)
    {
        $this->validate($request, $this->loanRepository->rules());
        $request->merge([
            'remain_amount' => $request->amount,
            'user_id' => auth(GUARD_CUSTOMER)->id(),
            'status' => PENDING,
            'payment_amount' => ceil($request->amount/$request->terms)
        ]);
        $loan = $this->loanRepository->create($request->only(['remain_amount', 'user_id', 'status', 'amount', 'terms', 'payment_amount']));
        return $this->sendSuccess(__('messages.save', ['model' => __('messages.tables.loan')]), new LoanClientResources($loan));
    }

    public function updateDay($id, Request $request)
    {
        $loan = $this->loanRepository->find($id);
        if(!$loan || $loan->user_id != auth(GUARD_CUSTOMER)->id()) {
            return $this->sendError(__('messages.not_found',  ['model' => __('messages.tables.loan')]));
        }
        if ($loan->status != APPROVE) {
            return $this->sendError('Yêu cầu đang không ở trạng thái chấp nhận');
        }
        if ($loan->day_of_week_payment !== null) {
            return $this->sendError('Bạn đã cập nhật ngày thanh toán rồi');
        }
        $this->validate($request, ['day' => 'required|integer|min:0|max:6']);
        $loan = $this->loanRepository->update([
            'day_of_week_payment' => $request->day
        ], $id);
        return $this->sendSuccess(__('messages.update_success',['model' => __('messages.tables.loan')]), new LoanClientResources($loan));
    }

    public function payment($id, Request $request)
    {
        $loan = $this->loanRepository->find($id);
        if(!$loan || $loan->user_id != auth(GUARD_CUSTOMER)->id()) {
            return $this->sendError(__('messages.not_found',  ['model' => __('messages.tables.loan')]));
        }
        if ($loan->day_of_week_payment === null) {
            return $this->sendError('Bạn chưa cập nhật ngày thanh toán');
        }
        $now = Carbon::now()->dayOfWeek;
        if ($now != (int)$loan->day_of_week_payment) {
            return $this->sendError('Bạn chưa đến ngày thanh toán trong tuần');
        }

        $checkHistory = $this->loanHistoryRepository->checkLoanByDay($id);
        if ($checkHistory) {
            return $this->sendError('Hôm nay bạn đã thanh toán rồi!');
        }
        $this->validate($request, ['amount' => 'required|integer|min:1']);

        if ($request->amount < $loan->payment_amount) {
            return $this->sendError('Vui lòng thanh toán số tiền tối thiểu theo tuần!');
        }
        $loan = $this->loanRepository->updateMoney($loan, $request->amount);
        if ($loan) {
            return $this->sendSuccess('Thanh toán thành công!', $request->amount);
        }
        return $this->sendError('Thanh toán thất bại! Vui lòng liên hệ admin');
    }
}
