<?php

namespace App\Repositories;

use App\Models\Loan;
use App\Models\LoanHistory;
use Illuminate\Support\Facades\DB;

class LoanRepository extends BaseRepository
{
    public function getFieldsSearchable()
    {
        return [];
    }

    public function model()
    {
        return Loan::class;
    }

    public function rules()
    {
        return [
            'amount' => 'required|integer|min:1',
            'terms' => 'required|integer|min:1'
        ];
    }

    public function genPaginateChild($params, $data)
    {
        $perPage = isset($params['per_page']) && (int)$params['per_page'] > 0 ? $params['per_page'] : DEFAULT_PAGE;
        $current_page = isset($params['current_page']) && (int)$params['current_page'] > 0 ? $params['current_page'] : DEFAULT_CURRENT_PAGE;
        $items = $data ? $data->orderBy('updated_at','DESC')
            ->when($params['user_id'], function ($query, $user_id){
                return $query->where('user_id', $user_id);
            })
            ->when($params['status'], function ($query, $status){
                return $query->where('status', $status);
            })
            ->paginate($perPage,['*'], 'current_page', $current_page) : '';

        return [
            'pagination' => [
                'current_page' => (int)$current_page,
                'next_page' => (int)$current_page < $items->lastPage() ? $current_page + 1 : null,
                'prev_page' => (int)$current_page > 1 ? $current_page - 1 : null,
                'per_page' => (int)$perPage,
                'total_page' => (int)$items->lastPage(),
            ],
            'filter' => $this->filterData($params),
            'items' => $items->items(),
        ];
    }

    public function genPaginateChildClient($params, $data)
    {
        $perPage = isset($params['per_page']) && (int)$params['per_page'] > 0 ? $params['per_page'] : DEFAULT_PAGE;
        $current_page = isset($params['current_page']) && (int)$params['current_page'] > 0 ? $params['current_page'] : DEFAULT_CURRENT_PAGE;
        $items = $data ? $data->orderBy('updated_at','DESC')
            ->where('user_id', auth(GUARD_CUSTOMER)->id())
            ->when($params['status'], function ($query, $status){
                return $query->where('status', $status);
            })
            ->paginate($perPage,['*'], 'current_page', $current_page) : '';

        return [
            'pagination' => [
                'current_page' => (int)$current_page,
                'next_page' => (int)$current_page < $items->lastPage() ? $current_page + 1 : null,
                'prev_page' => (int)$current_page > 1 ? $current_page - 1 : null,
                'per_page' => (int)$perPage,
                'total_page' => (int)$items->lastPage(),
            ],
            'filter' => $this->filterData($params),
            'items' => $items->items(),
        ];
    }

    public function updateMoney($loan, $money)
    {
        DB::beginTransaction();
        try {
            $remainAmount = (int)$loan->remain_amount >= (int)$money ?  (int)$loan->remain_amount -  (int)$money : 0;
            $loan->update([
                'remain_amount' => $remainAmount,
                'status' => $remainAmount == 0 ? DONE : PAYING
            ]);
            LoanHistory::create([
                'loan_id' => $loan->id,
                'money' => $money
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
}
