<?php

if (!function_exists('formatDateApp')) {
    function formatDateApp($string)
    {
        $arr = [
            0 => 'Thứ hai',
            1 => 'Thứ ba',
            2 => 'Thứ tư',
            3 => 'Thứ năm',
            4 => 'Thứ sáu',
            5 => 'Thứ bảy',
            6 => 'Chủ nhật',
        ];

        $date = $arr[date('w', $string)].' '.date("d/m/Y", $string);

        return $date;
    }
}

if (!function_exists('currency_format')) {
    function currency_format($number, $suffix = 'đ') {
        if (!empty($number)) {
            return number_format($number, 0, ',', '.') . "{$suffix}";
        }
    }
}

if (! function_exists('escape_like')) {
    /**
     * @param $string
     * @return mixed
     */
    function escape_like($string)
    {
        $search = array('!');
        $replace   = array('\!');
        return str_replace($search, $replace, $string);
    }
}

