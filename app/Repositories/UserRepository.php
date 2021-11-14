<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function getFieldsSearchable()
    {
        return [];
    }

    public function model()
    {
        return User::class;
    }

    public function rules($params, $id = '')
    {
        $id = !empty($id) ? $id : '';
        return [
            'username' => 'required|unique:users,username,'.$id,
            'name'     => 'required',
            'phone'    => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/|unique:users,phone,'.$id,
            'email'    => 'required|email|unique:users,email,'.$id,
            'password' => empty($id) || ( !empty($id) && $params['password'] ) ? 'required|confirmed|min:6' : '',
        ];
    }

    public function loginRules()
    {
        return [
            'password' => 'required|min:6|max:30',
            'username' => 'required|string|exists:users,username',
        ];
    }

    public function login($params)
    {
        $credentials = [
            'username'    => trim($params->username),
            'password' => $params->password,
            'type'     => User::TYPE_USER
        ];
        return auth(GUARD_CUSTOMER)->attempt($credentials);
    }
}
