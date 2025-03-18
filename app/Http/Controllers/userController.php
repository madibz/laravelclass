<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
 
class UserController extends Controller
{
 
    public function showLogin() {
        return view('auth.login');
    }
 
    public function showRegister() {
        return view('auth.register');
    }
 
    // public function showProfile() {
    //     return view('auth.profile');
    // }
 
    /**
     * Display a listing of the resource.
     */
    // Get All Users
    public function index()
    {
        try {
            return response()->json(User::all());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching users' . $e->getMessage()], 500);
        }
    }
 
    /**
     * Register a newly created resource in storage.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:4',
            ]);
   
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'bio' => $request->bio,
                'profile_picture' => $request->profile_picture
            ]);
 
            if ($request->wantsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'User registered successfully',
                    'user' => $user,
                    'token' => $token
                ], 201);
            }
           
            auth()->login($user);
            return redirect()->route('login')->with('success', 'Registration successful! Please login.');
 
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error creating user : '. $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error creating user: ' . $e->getMessage())->withInput();
        }
    }
 
    /**
     * Login a user.
     */
    public function login(Request $request) {
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
                    'message' => 'User logged in successfully',
                    'user' => $user,
                    'token' => $token
                ], 200);
            }
           
            return redirect()->route('profile')->with('success', 'Logged in successfully!');
        }
       
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
       
        return redirect()->back()->with('error', 'Invalid credentials')->withInput();
    }
 
    /**
     * Logout the current logged in user
     */
    public function logout(Request $request) {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }
 
    /**
     * Web logout method
     */
    public function webLogout() {
        auth()->logout();
        return redirect()->route('home')->with('success', 'Logged out successfully');
    }
 
    /**
     * Get current logged in user information
     */
    public function me(Request $request) {
        return response()->json($request->user());
    }
 
    /**
     * Display the specified resource.
     */
    // Get One User
    public function show(User $user)
    {
        return response()->json($user);
    }
 
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
 
            $validatedData = $request->validate([
                'username' => 'string|max:255|unique:users,username,' . $user->id,
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'string|min:4',
            ]);
 
            if($request->filled('password')) {
                $validatedData['password'] = bcrypt($request->password);
            }
            if (!empty($validatedData)) {
                $user->update($validatedData);
            }
            $user->refresh();
            return response()->json(['message' => 'User updated', 'user' => $user], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating user : '. $e->getMessage()], 500);
        }
    }
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
   
            $user->delete();
            return response()->json(['message' => 'User deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting user: ' . $e->getMessage()], 500);
        }
    }
 
    public function profile(Request $request) {
        $user = auth()->user();
        if(!$user) {
            return redirect()->route('login')->with('error', 'You are not logged in');
        }
        return view('auth.profile', compact('user'));
    }
   
}