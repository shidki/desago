<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    //
    public function edit_profile(Request $request){
        try {
            $request->validate([
                'name' => "required|max:255",
                'username' => "required|max:255",
                'phone' => "required|max:13",
                'email' => "required|email",
            ]);
            $user = User::where("email",'=',$request->email)->first();
            //dd($request->email);
            
            if($user->username != $request->username){
                $cekUser = User::where("username",'=',$request->username)->where("id",'!=',$user->id)->first();
                if($cekUser){
                    return response()->json([
                        "success" => false,
                        "message" => "Username telah digunakan"
                    ],442);
                }
            }
            if($user->phone != $request->phone){
                $cekUser = User::where("phone",'=',$request->phone)->where("id",'!=',$user->id)->first();
                if($cekUser){
                    return response()->json([
                        "success" => false,
                        "message" => "Nomor HP telah digunakan"
                    ],442);
                }
            }
            
            // email tidak diupdate karna email udah pasti sama ( di front end di disable edit email )
            $updateUser = DB::table('app_users')->where("id",'=',$user->id)->update([
                "name" => $request->name,
                "phone" => $request->phone,
                "username" => $request->username,
            ]);

            if($updateUser){
                return response()->json([
                    "success" => true,
                    "message" => "Berhasil mengubah profile",
                    'data' => $request->all()
                ],201);
            }else{
                return response()->json([
                    "success" => false,
                    "message" => "Gagal mengubah profile",
                ],442);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    }


}
