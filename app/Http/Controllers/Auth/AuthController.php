<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        try {


            if ($request->hasFile('imag_profile')) {

                $folder = '/images/';
                $disk = 'public';
                $file = $request->file('imag_profile');
                $fullName = $request->input('first_name') . "-" . $request->input('last_name');

                $name = Str::slug($fullName);
                $filePath = $name . '.' . $file->getClientOriginalExtension();
                $file->storeAs($folder, $name . '.' . $file->getClientOriginalExtension(), $disk);
            } else {
                $filePath = "";
            }


            $validate = Validator::make($request->all(),
                [
                    'email' => 'required|email|unique:users'
                ]);

            if ($validate->fails()) {
                return response()->json($validate, 400);
            }


            $user = User::create([
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "description" => $request->input('description'),
                "email" => $request->input('email'),
                "password" => bcrypt($request->input('password'))
            ]);

            $profile = User::find($user->id);
            if ($request->hasFile('imag_profile')) {
                $asso = new Profile([
                    "imag_profile" => $filePath
                ]);
                $profile->profile()->save($asso);
            }

            $token = $user->createToken('prueba_amoba')->accessToken;

            return response()->json(['user' => $user, 'token' => $token]);
        } catch (Exception $e) {
            $data = ['message' => $e->getMessage()];
            return response()->json($data, 400);
        }
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details.
            Please try again']);
        }

//        $token = auth()->user()->createToken('API Token')->accessToken;
        $token = auth()->user()->createToken('prueba_amoba')->accessToken;

        return response(['user' => auth()->user(), 'token' => $token]);

    }

}
