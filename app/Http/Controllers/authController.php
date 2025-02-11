<?php

namespace App\Http\Controllers;

use App\Models\socialMedia;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

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




    //public function sendResetLink(Request $request)
    //{
    //    $request->validate([
    //        'email' => 'required|email|exists:users,email',
    //    ]);

    //    $status = Password::sendResetLink($request->only('email'));
    //    $token = DB::table('password_reset_tokens')
    //    ->where('email', $request->email)
    //    ->orderBy('created_at', 'desc') // Ambil token terbaru
    //    ->value('token');
    //    $resetUrl = url('/reset-password/' . $token);
    //    if ($status === Password::RESET_LINK_SENT) {
    //        return response()->json([
    //            'success' => true,
    //            'message' => __($status),
    //            'reset_url' => $resetUrl // Kirim URL reset password yang benar
    //        ]);
    //    }

    //    return response()->json([
    //        'success' => false,
    //        'message' => __($status)
    //    ], 400);
    //}

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    
        // Ambil user berdasarkan email
        $user = User::where('email', $request->email)->first();
    
        // Buat token reset password
        $status = Password::createToken($user);
    
        // Kirim email dengan notifikasi kustom
        $user->notify(new ResetPasswordNotification($status));
    
        return response()->json([
            'success' => true,
            'message' => 'Email reset password telah dikirim.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        try {
            //dd($request->all());
            $request->validate([
                '_token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required',
                'password_confirmation' => 'required',
            ]);
            //dd(7);

            if($request->password !== $request->password_confirmation){
                return redirect()->back()->with('error', 'Password tidak sesuai');
            }
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
                return redirect()->back()->with('success', 'Password berhasil direset.');
                //return response()->json([
                //    'success' => true,
                //    'message' => __($status)
                //]);
            }
            return redirect()->back()->with('error', 'Gagal mereset password. Silakan coba lagi.');
            //return response()->json([
            //    'success' => false,
            //    'message' => __($status)
            //], 400);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ]);
        }
    }

    public function googleLogin(Request $request)
{
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required',
            'google_id' => 'required',
            'access_token' => 'required',
            'avatar' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
            try {
                // Cek apakah ada social account dengan google_id tersebut
                $socialAccount = socialMedia::where('provider', 'google')
                    ->where('provider_id', $request->google_id)
                    ->first();

                if ($socialAccount) {
                    // Jika ada, gunakan user yang terkait
                    $user = $socialAccount->user;
                } else {
                    // Cek apakah email sudah terdaftar
                    $user = User::where('email', $request->email)->first();

                    if (!$user) {
                        // Buat user baru jika belum ada
                        $user = User::create([
                            'name' => $request->name,
                            'username' => $request->name,
                            'email' => $request->email,
                            'password' => bcrypt(Str::random(16))
                        ]);
                    }

                    // Buat social account baru
                    $user->socialAccounts()->create([
                        'provider' => 'google',
                        'provider_id' => $request->google_id,
                        'avatar' => $request->avatar
                    ]);
                }

                // Update token social account
                if ($socialAccount) {
                    $socialAccount->update([
                        'avatar' => $request->avatar
                    ]);
                }

                // Generate token untuk API
                $token = $user->createToken('auth_token')->plainTextToken;

                DB::commit();

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user->load('socialAccounts')
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error during login',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
}
