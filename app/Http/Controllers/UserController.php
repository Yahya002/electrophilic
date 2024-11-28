<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function owner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'phone_number' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => 'Invalid Request']);
        }

        $validated = $validator->safe()->all();

        $user = new User($validated);
        $user->role = 'owner';
        $user->save();

        return $user;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => 'Invalid Request']);
        }

        $validated = $validator->safe()->all();

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
            
        $user = Auth::user();

        $token = $user->createToken($user->role)->plainTextToken;
            
        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = Auth::user();
        if ($currentUser->hasOwnerPerms())
        {
            return User::all();  
        }
        else
        {
            return response()->json(['message' => 'Unauthorized Request'], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'phone_number' => 'required|string|unique:users',
            'password' => 'required',
            'role' => 'required|string|in:manager,employee',
            'base_salary' => 'required|decimal:0,2|min:0',
            'status' => 'required|string|in:ACTIVE,SUSPENED,ON_LEAVE,TERMINATED',
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => 'Invalid Request']);
        }

        $currentUser = Auth::user();
        if ($currentUser->hasOwnerPerms())
        {
            $validated = $validator->safe()->all();
    
            $user = new User($validated);
            $user->save();
    
            $token = $user->createToken($user->role)->plainTextToken;
    
            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        }

        return response()->json(['message' => 'Unauthorized Request'], 403);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $userId)
    {
        $currentUser = Auth::user();
        if ($currentUser->hasOwnerPerms() || $currentUser->id == $userId)
        {
            return User::findOrFail($userId);  
        }
        else
        {
            return response()->json(['message' => 'Unauthorized Request'], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $userId)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string',
            'phone_number' => 'string',
            'password' => 'string',
            'role' => 'string|in:manager,employee',
            'base_salary' => 'decimal:0,2|min:0',
            'status' => 'string|in:ACTIVE,SUSPENED,ON_LEAVE,TERMINATED',
        ]);
        
        if ($validator->fails())
        {
            return response()->json(['message' => 'Invalid Request']);
        }

        $currentUser = Auth::user();
        $user = User::findOrFail($userId);
        if ($currentUser->hasOwnerPerms())
        {
            $validated = $validator->safe()->only(['base_salary', 'role', 'status']);
        }
        elseif ($currentUser->id == $userId)
        {
            $employee = Auth::user();
            $validated = $validator->safe()->only(['username', 'phone_number', 'password']);
        }
        else 
        {
            return response()->json(['message' => 'Unauthorized Request'], 403);
        }
        
        $user->update($validated);
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $userId)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($userId);
        if ($currentUser->hasOwnerPerms())
        {
            $user->delete();
            return response()->json(['message' => 'User Successfully Deleted'], 200);
        }

        return response()->json(['message' => 'Unauthorized Request'], 403);
    }
}
