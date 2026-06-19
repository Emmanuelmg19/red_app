<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::with('image')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email ?? null,
            'phone'    => $request->phone,
            'password' => bcrypt('default_password'),
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $user->image()->create([
                'url' => Storage::url($path),
            ]);
        }

        return (new UserResource($user->load('image')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        return new UserResource($user->load('image'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user->update([
            'name'  => $request->input('name',  $user->name),
            'email' => $request->input('email', $user->email),
            'phone' => $request->input('phone', $user->phone),
        ]);

        if ($request->hasFile('image')) {
            if ($user->image) {
                $old = str_replace('/storage/', '', $user->image->url);
                Storage::disk('public')->delete($old);
                $user->image()->delete();
            }
            $path = $request->file('image')->store('images', 'public');
            $user->image()->create([
                'url' => Storage::url($path),
            ]);
        }

        return new UserResource($user->load('image'));
    }

    public function destroy(User $user)
    {
        if ($user->image) {
            $old = str_replace('/storage/', '', $user->image->url);
            Storage::disk('public')->delete($old);
            $user->image()->delete();
        }

        $user->delete();

        return response()->noContent();
    }
}