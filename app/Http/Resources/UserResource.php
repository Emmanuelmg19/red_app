<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'image_count' => $this->whenCounted('images'),
            'thumbnail'   => $this->whenLoaded('images', function () {
                $first = $this->images->first();
                return $first ? $first->url : null;
            }),
        ];
    }
}