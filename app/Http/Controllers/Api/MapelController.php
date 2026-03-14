<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Model\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MapelController extends Controller
{
    private function collectionResponse($items, $request, $template = null)
    {
        return response()->json([
            'collection' => [
                'version' => '1.0',
                'href'    => $request->url(),
                'items'   => $items,
                'links'   => [],
                'queries' => [],
                'template' => $template ?? [
                    'data' => [
                        ['name' => 'kode_mapel', 'value' => '', 'prompt' => 'Kode Mapel'],
                        ['name' => 'nama_mapel', 'value' => '', 'prompt' => 'Nama Mapel'],
                    ]
                ],
            ]
        ], 200);
    }

    private function itemToCollection($mapel, $request)
    {
        return [
            'href' => $request->root() . '/api/mapel/' . $mapel->id,
            'data' => [
                ['name' => 'id',         'value' => $mapel->id,         'prompt' => 'ID'],
                ['name' => 'kode_mapel', 'value' => $mapel->kode_mapel, 'prompt' => 'Kode Mapel'],
                ['name' => 'nama_mapel', 'value' => $mapel->nama_mapel, 'prompt' => 'Nama Mapel'],
                ['name' => 'created_at', 'value' => $mapel->created_at, 'prompt' => 'Dibuat'],
                ['name' => 'updated_at', 'value' => $mapel->updated_at, 'prompt' => 'Diupdate'],
            ],
            'links' => [],
        ];
    }

    private function failedResponse($message, $statusCode)
    {
        return response()->json([
            'collection' => [
                'version' => '1.0',
                'error'   => [
                    'title'   => 'Error',
                    'message' => $message,
                    'code'    => $statusCode,
                ],
            ]
        ], $statusCode);
    }

    // GET /api/mapel
    public function index(Request $request)
    {
        $mapels = Mapel::all();
        $items  = $mapels->map(fn($m) => $this->itemToCollection($m, $request))->toArray();
        return $this->collectionResponse($items, $request);
    }

    // POST /api/mapel
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_mapel' => 'required|unique:mapel,kode_mapel',
            'nama_mapel' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $mapel = Mapel::create($request->all());
        $items = [$this->itemToCollection($mapel, $request)];
        return $this->collectionResponse($items, $request);
    }

    // GET /api/mapel/{id}
    public function show(Request $request, $id)
    {
        $mapel = Mapel::find($id);
        if (!$mapel) return $this->failedResponse('Mapel tidak ditemukan!', 404);

        $items = [$this->itemToCollection($mapel, $request)];
        return $this->collectionResponse($items, $request);
    }

    // PUT /api/mapel/{id}
    public function update(Request $request, $id)
    {
        $mapel = Mapel::find($id);
        if (!$mapel) return $this->failedResponse('Mapel tidak ditemukan!', 404);

        $validator = Validator::make($request->all(), [
            'kode_mapel' => 'required|unique:mapel,kode_mapel,' . $mapel->id,
            'nama_mapel' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $mapel->kode_mapel = $request->kode_mapel;
        $mapel->nama_mapel = $request->nama_mapel;
        $mapel->save();

        $items = [$this->itemToCollection($mapel, $request)];
        return $this->collectionResponse($items, $request);
    }

    // DELETE /api/mapel/{id}
    public function destroy(Request $request, $id)
    {
        $mapel = Mapel::find($id);
        if (!$mapel) return $this->failedResponse('Mapel tidak ditemukan!', 404);

        $mapel->delete();
        return $this->collectionResponse([], $request);
    }
}