<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Model\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
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
                        ['name' => 'nis',          'value' => '', 'prompt' => 'NIS'],
                        ['name' => 'nama',         'value' => '', 'prompt' => 'Nama'],
                        ['name' => 'gender',       'value' => '', 'prompt' => 'Gender (laki-laki/perempuan)'],
                        ['name' => 'tempat_lahir', 'value' => '', 'prompt' => 'Tempat Lahir'],
                        ['name' => 'tgl_lahir',    'value' => '', 'prompt' => 'Tanggal Lahir'],
                        ['name' => 'email',        'value' => '', 'prompt' => 'Email'],
                        ['name' => 'nama_ortu',    'value' => '', 'prompt' => 'Nama Orang Tua'],
                        ['name' => 'phone_number', 'value' => '', 'prompt' => 'Nomor HP'],
                        ['name' => 'alamat',       'value' => '', 'prompt' => 'Alamat'],
                        ['name' => 'kelas_id',     'value' => '', 'prompt' => 'ID Kelas'],
                    ]
                ],
            ]
        ], 200);
    }

    private function itemToCollection($siswa, $request)
    {
        return [
            'href' => $request->root() . '/api/siswa/' . $siswa->id,
            'data' => [
                ['name' => 'id',           'value' => $siswa->id,           'prompt' => 'ID'],
                ['name' => 'nis',          'value' => $siswa->nis,          'prompt' => 'NIS'],
                ['name' => 'nama',         'value' => $siswa->nama,         'prompt' => 'Nama'],
                ['name' => 'gender',       'value' => $siswa->gender,       'prompt' => 'Gender'],
                ['name' => 'tempat_lahir', 'value' => $siswa->tempat_lahir, 'prompt' => 'Tempat Lahir'],
                ['name' => 'tgl_lahir',    'value' => $siswa->tgl_lahir,    'prompt' => 'Tanggal Lahir'],
                ['name' => 'email',        'value' => $siswa->email,        'prompt' => 'Email'],
                ['name' => 'nama_ortu',    'value' => $siswa->nama_ortu,    'prompt' => 'Nama Orang Tua'],
                ['name' => 'phone_number', 'value' => $siswa->phone_number, 'prompt' => 'Nomor HP'],
                ['name' => 'alamat',       'value' => $siswa->alamat,       'prompt' => 'Alamat'],
                ['name' => 'kelas_id',     'value' => $siswa->kelas_id,     'prompt' => 'ID Kelas'],
            ],
            'links' => [
                ['rel' => 'kelas', 'href' => $request->root() . '/api/kelas/' . $siswa->kelas_id],
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

    // GET /api/siswa
    public function index(Request $request)
    {
        $siswas = Siswa::with('kelas')->get();
        $items  = $siswas->map(fn($s) => $this->itemToCollection($s, $request))->toArray();
        return $this->collectionResponse($items, $request);
    }

    // POST /api/siswa
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string',
            'email'    => 'required|email|unique:siswa,email',
            'gender'   => 'required|in:laki-laki,perempuan',
            'nis'      => 'nullable|unique:siswa,nis',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $siswa = Siswa::create($request->all());
        $items = [$this->itemToCollection($siswa, $request)];
        return $this->collectionResponse($items, $request);
    }

    // GET /api/siswa/{id}
    public function show(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) return $this->failedResponse('Siswa tidak ditemukan!', 404);

        $items = [$this->itemToCollection($siswa, $request)];
        return $this->collectionResponse($items, $request);
    }

    // PUT /api/siswa/{id}
    public function update(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) return $this->failedResponse('Siswa tidak ditemukan!', 404);

        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string',
            'email'    => 'required|email|unique:siswa,email,' . $siswa->id,
            'gender'   => 'required|in:laki-laki,perempuan',
            'nis'      => 'nullable|unique:siswa,nis,' . $siswa->id,
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $siswa->update($request->all());
        $items = [$this->itemToCollection($siswa, $request)];
        return $this->collectionResponse($items, $request);
    }

    // DELETE /api/siswa/{id}
    public function destroy(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) return $this->failedResponse('Siswa tidak ditemukan!', 404);

        $siswa->delete();
        return $this->collectionResponse([], $request);
    }
}