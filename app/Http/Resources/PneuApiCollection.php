<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Resource Collection para lista de pneus
 */
class PneuApiCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($pneu) {
                return new PneuApiResource($pneu);
            }),
            'meta' => [
                'total' => $this->collection->count(),
                'timestamp' => now()->toISOString(),
            ]
        ];
    }
}