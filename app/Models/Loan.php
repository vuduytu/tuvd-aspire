<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Loan extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    protected $table = 'loans';
    protected $fillable = ['user_id', 'amount', 'remain_amount', 'status', 'approve_date', 'admin_id', 'terms', 'payment_unit', 'day_of_week_payment', 'payment_amount'];

    const STATUS_NAME = [
        PENDING => 'Chờ xác nhận',
        APPROVE => 'Đã chấp nhận',
        REJECT => 'Đã từ chối',
        PAYING => 'Đang trả',
        DONE => 'Đã hoàn thành'
    ];

    const DAY_WEEK = [
        1 => 'Thứ hai',
        2 => 'Thứ ba',
        3 => 'Thứ tư',
        4 => 'Thứ năm',
        5 => 'Thứ sáu',
        6 => 'Thứ bảy',
        0 => 'Chủ nhật',
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
