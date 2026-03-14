<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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
                        ['name' => 'type',     'value' => '', 'prompt' => 'Tipe User (admin/guru)'],
                        ['name' => 'username', 'value' => '', 'prompt' => 'Username'],
                        ['name' => 'password', 'value' => '', 'prompt' => 'Password'],
                    ]
                ],
            ]
        ], 200);
    }

    private function itemToCollection($user, $request)
    {
        return [
            'href' => $request->root() . '/api/users/' . $user->id,
            'data' => [
                ['name' => 'id',         'value' => $user->id,         'prompt' => 'ID'],
                ['name' => 'type',       'value' => $user->type,       'prompt' => 'Tipe User'],
                ['name' => 'username',   'value' => $user->username,   'prompt' => 'Username'],
                ['name' => 'created_at', 'value' => $user->created_at, 'prompt' => 'Dibuat'],
                ['name' => 'updated_at', 'value' => $user->updated_at, 'prompt' => 'Diupdate'],
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

    // GET /api/users
    public function index(Request $request)
    {
        $users = User::all();
        $items = $users->map(fn($u) => $this->itemToCollection($u, $request))->toArray();
        return $this->collectionResponse($items, $request);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'     => 'required|in:admin,guru',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $user           = new User();
        $user->type     = $request->type;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->save();

        $items = [$this->itemToCollection($user, $request)];
        return $this->collectionResponse($items, $request);
    }

    // GET /api/users/{id}
    public function show(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return $this->failedResponse('User tidak ditemukan!', 404);

        $items = [$this->itemToCollection($user, $request)];
        return $this->collectionResponse($items, $request);
    }

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return $this->failedResponse('User tidak ditemukan!', 404);

        $validator = Validator::make($request->all(), [
            'type'     => 'required|in:admin,guru',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'password' => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return $this->failedResponse($validator->errors(), 422);
        }

        $user->type     = $request->type;
        $user->username = $request->username;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $items = [$this->itemToCollection($user, $request)];
        return $this->collectionResponse($items, $request);
    }

    // DELETE /api/users/{id}
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return $this->failedResponse('User tidak ditemukan!', 404);

        $user->delete();
        return $this->collectionResponse([], $request);
    }
}