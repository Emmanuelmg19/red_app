<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // GET /api/users -> Index ligero: NO regresa la galería completa
    public function index()
    {
        $users = User::withCount('images')
            ->with(['images' => function ($q) {
                $q->latest()->limit(1); // solo para la miniatura
            }])
            ->get();

        return UserResource::collection($users);
    }

    // POST /api/users -> Crea contacto, acepta varias imágenes
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'required|string|max:20',
            'images'   => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => bcrypt('default_password'),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('images', 'public');
                $user->images()->create([
                    'url' => Storage::url($path),
                ]);
            }
        }

        return (new UserDetailResource($user->load('images')))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/users/{id} -> Show completo: SÍ regresa todas las imágenes
    public function show(User $user)
    {
        return new UserDetailResource($user->load('images'));
    }

    // PUT/PATCH /api/users/{id} -> Actualiza datos, puede agregar imágenes sin borrar las anteriores
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'sometimes|required|string|max:20',
            'images'   => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user->update([
            'name'  => $request->input('name',  $user->name),
            'email' => $request->input('email', $user->email),
            'phone' => $request->input('phone', $user->phone),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('images', 'public');
                $user->images()->create([
                    'url' => Storage::url($path),
                ]);
            }
        }

        return new UserDetailResource($user->load('images'));
    }

    // DELETE /api/users/{id} -> Elimina contacto y sus imágenes
    public function destroy(User $user)
    {
        foreach ($user->images as $image) {
            $path = str_replace('/storage/', '', $image->url);
            Storage::disk('public')->delete($path);
        }
        $user->images()->delete();
        $user->delete();

        return response()->noContent(); // 204
    }

    // POST /api/users/{id}/images -> Agrega imágenes a un contacto existente
    public function addImages(Request $request, User $user)
    {
        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        foreach ($request->file('images') as $file) {
            $path = $file->store('images', 'public');
            $user->images()->create([
                'url' => Storage::url($path),
            ]);
        }

        return (new UserDetailResource($user->load('images')))
            ->response()
            ->setStatusCode(201);
    }

    // DELETE /api/images/{id} -> Elimina una imagen sin eliminar el contacto
    public function deleteImage(Image $image)
    {
        $path = str_replace('/storage/', '', $image->url);
        Storage::disk('public')->delete($path);
        $image->delete();

        return response()->noContent(); // 204
    }
}