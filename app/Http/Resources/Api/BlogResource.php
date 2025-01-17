<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'slug'                  => $this->slug,
            'url'                   => route('api.post.details',$this->id),
            'title'                 => $this->getTranslation('title',apiLanguage($request->lang)),
            'short_description'     => nullCheck($this->getTranslation('short_description',apiLanguage($request->lang))),
            'thumbnail'             => $this->thumbnail,
        ];
    }
}
