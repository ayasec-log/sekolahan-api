<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Model\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuruController extends Controller
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
                        ['name' => 'user_id',      'value' => '', 'prompt' => 'User ID'],
                        ['name' => 'nip',          'value' => '', 'prompt' => 'NIP'],
                        ['name' => 'nama',         'value' => '', 'prompt' => 'Nama'],
                        ['name' => 'email',        'value' => '', 'prompt' => 'Email'],
                        ['name' => 'gender',       'value' => '', 'prompt' => 'Gender (laki-laki/perempuan)'],
                        ['name' => 'phone_number', 'value' => '', 'prompt' => 'Nomor HP'],
                        ['name' => 'tempat_lahir', 'value' => '', 'prompt' => 'Tempat Lahir'],
                        ['name' => 'tgl_lahir',    'value' => '', 'prompt' => 'Tanggal Lahir'],
                        ['name' => 'alamat',       'value' => '', 'prompt' => 'Alamat'],
                        ['name' => 'pendidikan',   'value' => '', 'prompt' => 'Pendidikan'],
                    ]
                ],
            ]
        ], 200);
    }

    private function itemToCollection($guru, $request)
    {
        return [
            'href' => $request->root() . '/api/guru/' . $guru->id,
            'data' => [
                ['name' => 'id',           'value' => $guru->id,           'prompt' => 'ID'],
                ['name' => 'user_id',      'value' => $guru->user_id,      'prompt' => 'User ID'],
                ['name' => 'nip',          'value' => $guru->nip,          'prompt' => 'NIP'],
                ['name' => 'nama',         'value' => $guru->nama,         'prompt' => 'Nama'],
                ['name' => 'email',        'value' => $guru->email,        'prompt' => 'Email'],
                ['name' => 'gender',       'value' => $guru->gender,       'prompt' => 'Gender'],
                ['name' => 'phone_number', 'value' => $guru->phone_number, 'prompt' => 'Nomor HP'],
                ['name' => 'tempat_lahir', 'value' => $guru->tempat_lahir, 'prompt' => 'Tempat Lahir'],
                ['name' => 'tgl_lahir',    'value' => $guru->tgl_lahir,    'prompt' => 'Tanggal Lahir'],
                ['name' => 'alamat',       'value' => $guru->alamat,       'prompt' => 'Alamat'],
                ['name' => 'pendidikan',   'value' => $guru->pendidikan,   'prompt' => 'Pendidikan'],
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

    // GET /api/guru
    public function index(Request $request)
    {
        $gurus = Guru::with('user')->get();
        $items = $gurus->map(fn($g) => $this->itemToCollection($g, $request))->toArray();
        return $this->collectionResponse($items, $request);
    }

    // POST /api/guru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'nama'    => 'required|string',
            'email'   => 'required|email|unique:guru,email',
            'gender'  => 'required|in:laki-laki,perempuan',
            'nip'     => 'nullable|unique:guru,nip',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $guru  = Guru::create($request->all());
        $items = [$this->itemToCollection($guru, $request)];
        return $this->collectionResponse($items, $request);
    }

    // GET /api/guru/{id}
    public function show(Request $request, $id)
    {
        $guru = Guru::find($id);
        if (!$guru) return $this->failedResponse('Guru tidak ditemukan!', 404);

        $items = [$this->itemToCollection($guru, $request)];
        return $this->collectionResponse($items, $request);
    }

    // PUT /api/guru/{id}
    public function update(Request $request, $id)
    {
        $guru = Guru::find($id);
        if (!$guru) return $this->failedResponse('Guru tidak ditemukan!', 404);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'nama'    => 'required|string',
            'email'   => 'required|email|unique:guru,email,' . $guru->id,
            'gender'  => 'required|in:laki-laki,perempuan',
            'nip'     => 'nullable|unique:guru,nip,' . $guru->id,
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $guru->update($request->all());
        $items = [$this->itemToCollection($guru, $request)];
        return $this->collectionResponse($items, $request);
    }

    // DELETE /api/guru/{id}
    public function destroy(Request $request, $id)
    {
        $guru = Guru::find($id);
        if (!$guru) return $this->failedResponse('Guru tidak ditemukan!', 404);

        $guru->delete();
        return $this->collectionResponse([], $request);
    }
}