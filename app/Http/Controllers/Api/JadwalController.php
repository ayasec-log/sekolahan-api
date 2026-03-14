<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Model\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
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
                        ['name' => 'kelas_id',      'value' => '', 'prompt' => 'ID Kelas'],
                        ['name' => 'mapel_id',      'value' => '', 'prompt' => 'ID Mapel'],
                        ['name' => 'guru_id',       'value' => '', 'prompt' => 'ID Guru'],
                        ['name' => 'hari',          'value' => '', 'prompt' => 'Hari (senin-sabtu)'],
                        ['name' => 'jam_pelajaran', 'value' => '', 'prompt' => 'Jam Pelajaran'],
                    ]
                ],
            ]
        ], 200);
    }

    private function itemToCollection($jadwal, $request)
    {
        return [
            'href' => $request->root() . '/api/jadwal/' . $jadwal->id,
            'data' => [
                ['name' => 'id',           'value' => $jadwal->id,           'prompt' => 'ID'],
                ['name' => 'kelas_id',     'value' => $jadwal->kelas_id,     'prompt' => 'ID Kelas'],
                ['name' => 'mapel_id',     'value' => $jadwal->mapel_id,     'prompt' => 'ID Mapel'],
                ['name' => 'guru_id',      'value' => $jadwal->guru_id,      'prompt' => 'ID Guru'],
                ['name' => 'hari',         'value' => $jadwal->hari,         'prompt' => 'Hari'],
                ['name' => 'jam_pelajaran','value' => $jadwal->jam_pelajaran,'prompt' => 'Jam Pelajaran'],
                ['name' => 'created_at',   'value' => $jadwal->created_at,   'prompt' => 'Dibuat'],
                ['name' => 'updated_at',   'value' => $jadwal->updated_at,   'prompt' => 'Diupdate'],
            ],
            'links' => [
                ['rel' => 'kelas', 'href' => $request->root() . '/api/kelas/' . $jadwal->kelas_id],
                ['rel' => 'mapel', 'href' => $request->root() . '/api/mapel/' . $jadwal->mapel_id],
                ['rel' => 'guru',  'href' => $request->root() . '/api/guru/'  . $jadwal->guru_id],
            ],
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

    // GET /api/jadwal
    public function index(Request $request)
    {
        $jadwals = Jadwal::with(['kelas', 'mapel', 'guru'])->get();
        $items   = $jadwals->map(fn($j) => $this->itemToCollection($j, $request))->toArray();
        return $this->collectionResponse($items, $request);
    }

    // POST /api/jadwal
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_id'      => 'required|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapel,id',
            'guru_id'       => 'required|exists:guru,id',
            'hari'          => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_pelajaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $jadwal = Jadwal::create($request->all());
        $items  = [$this->itemToCollection($jadwal, $request)];
        return $this->collectionResponse($items, $request);
    }

    // GET /api/jadwal/{id}
    public function show(Request $request, $id)
    {
        $jadwal = Jadwal::find($id);
        if (!$jadwal) return $this->failedResponse('Jadwal tidak ditemukan!', 404);

        $items = [$this->itemToCollection($jadwal, $request)];
        return $this->collectionResponse($items, $request);
    }

    // PUT /api/jadwal/{id}
    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::find($id);
        if (!$jadwal) return $this->failedResponse('Jadwal tidak ditemukan!', 404);

        $validator = Validator::make($request->all(), [
            'kelas_id'      => 'required|exists:kelas,id',
            'mapel_id'      => 'required|exists:mapel,id',
            'guru_id'       => 'required|exists:guru,id',
            'hari'          => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_pelajaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $jadwal->update($request->all());
        $items = [$this->itemToCollection($jadwal, $request)];
        return $this->collectionResponse($items, $request);
    }

    // DELETE /api/jadwal/{id}
    public function destroy(Request $request, $id)
    {
        $jadwal = Jadwal::find($id);
        if (!$jadwal) return $this->failedResponse('Jadwal tidak ditemukan!', 404);

        $jadwal->delete();
        return $this->collectionResponse([], $request);
    }
}