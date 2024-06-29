<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResponse extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "username" => $this->username,
            "role" => $this->role,
            'token' => $this->whenNotNull($this->token)
        ];
    }
}