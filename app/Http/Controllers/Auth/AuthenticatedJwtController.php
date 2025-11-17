<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\SignInRequest;
use App\Modules\Configuracoes\Models\User;

class AuthenticatedJwtController extends Controller
{
    protected $userModel;

    function __construct(User $user)
    {
        $this->userModel = $user;
    }

    public function signIn(SignInRequest $request)
    {

        try{
            $user = $this->userModel->where('email', $request->email)->first();
    
            if(!$user){
                return response()->json(['message' => 'Email ou senha incorretos.'], 401);
            }
        
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Email ou senha incorretos.'], 401);
            }

            $user->tokens->each(function ($token) {
                if($token){
                    $token->delete();
                }
            });
        
            $token = $user->createToken($user->id)->plainTextToken;
        
            return response()->json(['token'=> $token]);
        }catch(Exception $e){
            return response()->json(['message' => 'Email ou senha incorretos.'], 401);
        }
    }

    public function signOut(Request $request)
    {
        $tokenRecord = PersonalAccessToken::where('token', hash('sha256', $request->bearerToken()))->first();

        dd($user);
    }

    
}
