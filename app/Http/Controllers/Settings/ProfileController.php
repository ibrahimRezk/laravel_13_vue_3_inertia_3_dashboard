<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Settings\ProfileDeleteRequest;


class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {

        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($request->user()->avatar) {
                Storage::disk('public')->delete($request->user()->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');

            // $request->user()->avatar = $path;
        }

        $request->user()->fill($request->validated());
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            $request->user()->avatar = $path;
        }

        $request->user()->save();

        //      Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);
    
        //     return to_route('profile.edit');
        return to_route('profile.edit')->with('success', 'profile updated successfully');

    }

    /**
     * Delete the user's profile.
     */
   public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        
        $user = $request->user();
        
        // dd($user->avatar);
        // dd('heeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeere');
        if ($user->avatar) {
            Storage::disk('public')->delete($request->user()->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }


    public function deleteAvatar(Request $request)
    {
        if ($request->user()->avatar) {
            Storage::disk('public')->delete($request->user()->avatar);

            $request->user()->avatar = null;
            $request->user()->save();
        }


         return back()->with('success', 'profile updated successfully');
    }
}
