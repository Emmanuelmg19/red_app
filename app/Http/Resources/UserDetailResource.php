<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'images'     => $this->images->map(function ($img) {
                return [
                    'id'  => $img->id,
                    'url' => $img->url,
                ];
            }),
        ];
    }
}