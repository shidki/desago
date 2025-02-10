<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
class authController extends Controller
{
    //
    public function login(Request $request){
        try {
            $credentials = $request->validate([
                'username' => "required|max:255",
                'password'=> "required",
            ]);

            $user = User::where("username",'=',$request->username)->first();
            if(!$user){
                return response()->json([
                    "success" => false,
                    'message' => "username tidak tersedia"
                ]);
            }

            if(! Hash::check($request->password, $user->password)){
                return response()->json([
                    "success" => false,
                    'message' => "password salah !"
                ]);
            }
            $user->tokens()->delete();

            
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                "success" => true,
                "message" => "Login Berhasil",
                "user" => $user,
                "token" => $token,
            ]);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                "message" => $e->getMessage()
            ]);
        }
    }

    public function register(Request $request){
        try {
            $request->validate([
                'name' => "required|max:255",
                'username' => "required|max:255",
                'phone' => "required|max:13",
                'email' => "required|email",
                'password'=> "required",
                'password_confirmation'=> "required",
            ]);

            $user = User::where('email','=',$request->email)->first();
            $username = User::where('username','=',$request->username)->first();
            
            if($user){
                return response()->json([
                    "success" => false,
                    'message' => "email tersedia"
                ]);
            }
            if($username){
                return response()->json([
                    "success" => false,
                    'message' => "username tersedia"
                ]);
            }

            if($request->password_confirmation == $request->password){

                
                $insert = User::create([
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                ]);
                if($insert){
                    $token = $insert->createToken('auth_token')->plainTextToken;
                    return response()->json([
                        "success" => true,
                        'message' => "berhasil mendaftar akun",
                        "data" => [
                            "name" => $request->name,
                            "username" => $request->username,
                            "phone" => $request->phone,
                            "email" => $request->email,
                            "password" => $request->password,
                            "password_confirmation" => $request->password_confirmation,
                            'token' => $token
                        ]
                    ],201);
                }
            }else{
                return response()->json([
                    "success" => false,
                    'message' => "password tidak sesuai"
                ]);
            }

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "message" => $th
            ]);
        }
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));
        $token = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->orderBy('created_at', 'desc') // Ambil token terbaru
        ->value('token');
        
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => __($status),
                'token' => $token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status)
        ], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => __($status)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status)
        ], 400);
    }
}
