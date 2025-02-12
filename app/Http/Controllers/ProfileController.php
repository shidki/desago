<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            dd(Auth::user());
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    }
}
