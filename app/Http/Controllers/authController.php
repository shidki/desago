<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
                    "status" => false,
                    'message' => "username tidak tersedia"
                ]);
            }

            if(! Hash::check($request->password, $user->password)){
                return response()->json([
                    "status" => false,
                    'message' => "password salah !"
                ]);
            }
            
            return response()->json([
                "status" => true,
                'message' => "Login Berhasil",
                'data' => $user
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
            ]);

            $user = User::where('email','=',$request->email)->first();
            $username = User::where('username','=',$request->username)->first();
            
            if($user){
                return response()->json([
                    "status" => false,
                    'message' => "email tersedia"
                ]);
            }
            if($username){
                return response()->json([
                    "status" => false,
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
                        "status" => true,
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
                    "status" => false,
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
}
