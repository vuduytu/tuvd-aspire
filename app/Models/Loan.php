<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Loan extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;
    protected $table = 'loans';
    protected $fillable = ['user_id', 'amount', 'remain_amount', 'status', 'approve_date', 'admin_id', 'terms', 'payment_unit', 'day_of_week_payment', 'payment_amount'];

    const STATUS_NAME = [
        PENDING => 'Pending',
        APPROVE => 'Approved',
        REJECT => 'Rejected',
        PAYING => 'Paying',
        DONE => 'Done'
    ];

    const DAY_WEEK = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        0 => 'Sunday',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
