<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Affiche la page de profil
     */
    public function profile()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }
        return view('auth.profile', compact('user'));
    }

    /**
     * Récupérer tous les utilisateurs (API)
     */
    public function index()
    {
        try {
            return response()->json(User::all());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:4',
                'bio' => 'nullable|string',
                'profile_picture' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048'
            ]);

            $profilePicturePath = null;

            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'bio' => $request->bio,
                'profile_picture' => $profilePicturePath
            ]);

            if ($request->wantsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Utilisateur créé avec succès',
                    'user' => $user,
                    'token' => $token
                ], 201);
            }

            auth()->login($user);
            return redirect()->route('profile')->with('success', 'Compte créé avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Connexion utilisateur
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            if ($request->wantsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Connexion réussie',
                    'user' => $user,
                    'token' => $token
                ], 200);
            }

            return redirect()->route('profile')->with('success', 'Connexion réussie!');
        }

        return redirect()->back()->with('error', 'Identifiants invalides')->withInput();
    }

    /**
     * Déconnexion utilisateur
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }

    /**
     * Déconnexion pour le web
     */
    public function webLogout()
    {
        auth()->logout();
        return redirect()->route('home')->with('success', 'Déconnexion réussie');
    }

    /**
     * Récupère les infos de l'utilisateur connecté (API)
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Afficher un utilisateur spécifique (API)
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Mise à jour du profil utilisateur
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Vous devez être connecté.');
            }

            $validatedData = $request->validate([
                'username' => 'string|max:255|unique:users,username,' . $user->id,
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:4',
                'bio' => 'nullable|string',
                'profile_picture' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048'
            ]);

            if ($request->filled('password')) {
                $validatedData['password'] = bcrypt($request->password);
            }

            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $validatedData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            $user->update($validatedData);
            return redirect()->route('profile')->with('success', 'Profil mis à jour.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Suppression du compte utilisateur
     */
    public function destroy()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();
        return redirect()->route('register')->with('success', 'Compte supprimé avec succès.');
    }
}
