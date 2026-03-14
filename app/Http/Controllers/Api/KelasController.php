<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Model\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
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
                        ['name' => 'kode_kelas', 'value' => '', 'prompt' => 'Kode Kelas'],
                        ['name' => 'nama_kelas', 'value' => '', 'prompt' => 'Nama Kelas'],
                    ]
                ],
            ]
        ], 200);
    }

    private function itemToCollection($kelas, $request)
    {
        return [
            'href' => $request->root() . '/api/kelas/' . $kelas->id,
            'data' => [
                ['name' => 'id',         'value' => $kelas->id,         'prompt' => 'ID'],
                ['name' => 'kode_kelas', 'value' => $kelas->kode_kelas, 'prompt' => 'Kode Kelas'],
                ['name' => 'nama_kelas', 'value' => $kelas->nama_kelas, 'prompt' => 'Nama Kelas'],
                ['name' => 'created_at', 'value' => $kelas->created_at, 'prompt' => 'Dibuat'],
                ['name' => 'updated_at', 'value' => $kelas->updated_at, 'prompt' => 'Diupdate'],
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

    // GET /api/kelas
    public function index(Request $request)
    {
        $kelas = Kelas::all();
        $items = $kelas->map(fn($k) => $this->itemToCollection($k, $request))->toArray();
        return $this->collectionResponse($items, $request);
    }

    // POST /api/kelas
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_kelas' => 'required|unique:kelas,kode_kelas',
            'nama_kelas' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $kelas = Kelas::create($request->all());
        $items = [$this->itemToCollection($kelas, $request)];
        return $this->collectionResponse($items, $request);
    }

    // GET /api/kelas/{id}
    public function show(Request $request, $id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) return $this->failedResponse('Kelas tidak ditemukan!', 404);

        $items = [$this->itemToCollection($kelas, $request)];
        return $this->collectionResponse($items, $request);
    }

    // PUT /api/kelas/{id}
    public function update(Request $request, $id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) return $this->failedResponse('Kelas tidak ditemukan!', 404);

        $validator = Validator::make($request->all(), [
            'kode_kelas' => 'required|unique:kelas,kode_kelas,' . $kelas->id,
            'nama_kelas' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $kelas->kode_kelas = $request->kode_kelas;
        $kelas->nama_kelas = $request->nama_kelas;
        $kelas->save();

        $items = [$this->itemToCollection($kelas, $request)];
        return $this->collectionResponse($items, $request);
    }

    // DELETE /api/kelas/{id}
    public function destroy(Request $request, $id)
    {
        $kelas = Kelas::find($id);
        if (!$kelas) return $this->failedResponse('Kelas tidak ditemukan!', 404);

        $kelas->delete();
        return $this->collectionResponse([], $request);
    }
}