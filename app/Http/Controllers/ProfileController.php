<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            // AGGIUNTO: Peschiamo le passkey dell'utente ordinate per data
            'passkeys' => $request->user()->webAuthnCredentials()->orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $request->user()->fill($request->validated());
        $request->user()->receives_notifications = $request->boolean('receives_notifications');

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Informazioni del profilo aggiornate con successo!']);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * ELIMINA UNA PASSKEY (Dispositivo Registrato)
     */
    public function destroyPasskey(Request $request, $id)
    {
        // Elimina l'impronta SOLO se appartiene a questo utente (sicurezza)
        $deleted = $request->user()->webAuthnCredentials()->where('id', $id)->delete();

        if ($deleted) {
            return response()->json(['message' => 'Dispositivo revocato con successo!']);
        }

        return response()->json(['message' => 'Impossibile trovare il dispositivo.'], 404);
    }
}