<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // Funzione helper per bloccare chi non è admin
    private function checkAdmin() {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Azione non autorizzata.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();
        $users = User::orderBy('name', 'asc')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['users' => $users]);
        }

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'data_nascita' => 'nullable|date',
            'password' => ['required', Password::defaults()],
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'data_nascita' => $validated['data_nascita'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_deleted' => false,
        ]);

        return response()->json(['message' => 'Utente creato con successo!']);
    }

    public function update(Request $request, User $user)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'data_nascita' => 'nullable|date',
            'role' => 'required|in:admin,user',
            'password' => ['nullable', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->data_nascita = $validated['data_nascita'];
        $user->role = $validated['role'];
        
        // Aggiorna la password solo se l'admin ne ha digitata una nuova
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        return response()->json(['message' => 'Utente aggiornato con successo!']);
    }

    public function toggleStatus(Request $request, User $user)
    {
        $this->checkAdmin();
        
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Non puoi disabilitare il tuo stesso account!'], 422);
        }

        $user->is_deleted = !$user->is_deleted;
        $user->save();

        $stato = $user->is_deleted ? 'Disabilitato' : 'Attivato';
        return response()->json(['message' => "L'utente è stato $stato!"]);
    }
}