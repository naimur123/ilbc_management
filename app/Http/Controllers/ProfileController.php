<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        $user->save();

        return back()->with('success', 'Profile updated.');
    }
}
