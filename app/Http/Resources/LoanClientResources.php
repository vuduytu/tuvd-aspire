<?php

namespace App\Http\Resources;

use App\Models\Loan;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanClientResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user_id,
            'user'              => $this->user,
            'status'            => $this->status,
            'status_name'       => Loan::STATUS_NAME[(int)$this->status],
            'amount'             => $this->amount,
            'remain_amount'       => $this->remain_amount,
            'terms'                => $this->terms,
            'day_of_week_payment'  => $this->day_of_week_payment,
            'day_name'  => $this->day_of_week_payment ? Loan::DAY_WEEK[$this->day_of_week_payment] : null,
            'payment_amount'       => $this->payment_amount,
        ];
    }
}
