<?php
namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'type'          => $this->type ?? User::TYPE_USER,
            'type_name'     => $this->type == User::TYPE_USER || $this->type == null
                                                        ? __('messages.roles.customer')
                                                        : __('messages.roles.admin_customer'),
            'email' => $this->email
        ];
    }
}
