<?php

namespace App\Http\Resources;

use App\Models\Loan;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanAdminResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                        => $this->id,
            'user_id'                   => $this->user_id,
            'user'                      => $this->user,
            'status'                    => $this->status,
            'status_name'               => Loan::STATUS_NAME[(int)$this->status],
            'money'                     => $this->money,
            'admin_id'                  => $this->admin_id,
            'admin'                     => $this->admin,
            'approve_date'              => $this->approve_date,
            'money_paid'                => $this->money_paid,
            'payment_period'            => $this->payment_period,
            'day_of_week_payment'       => $this->day_of_week_payment,
            'day_name'  => $this->day_of_week_payment ? Loan::DAY_WEEK[$this->day_of_week_payment] : null,
            'payment_amount'            => $this->payment_amount,
        ];
    }
}
