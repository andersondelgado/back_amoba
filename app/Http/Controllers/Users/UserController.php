<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //
    public function index(Request $request, $id = null)
    {

        if ($id == null) {
            if ($request->has('search')) {
                $search = strtolower($request->query('search'));
                $user = User::with('profile')
                    ->whereRaw('LOWER(`first_name`) LIKE ?', "%" . $search . "%")
                    ->orderBy('id', 'desc')
                    ->paginate(5);
                foreach ($user as $k => $usr) {
                    try {
                        if ($usr->profile != null) {

                            $file = ($usr->profile->imag_profile);
                            $usr->profile->imag_profile = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                            $usr->profile->image = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                            $user[$k]->profile = $usr->profile;
                        }
                    } catch (Exception $ef) {
                    }
                }

            } else if ($request->has('date')) {
                $date = Carbon::parse($request->query('date'));
                $now = Carbon::now();
                $user = User::with('profile')
//                    ->whereRaw('LOWER(`first_name`) LIKE ?', "%" . $search . "%")
                    ->whereBetween('created_at', [$date, $now])
                    ->orderBy('id', 'desc')
                    ->paginate(5);
                foreach ($user as $k => $usr) {
                    try {
                        if ($usr->profile != null) {

                            $file = ($usr->profile->imag_profile);
                            $usr->profile->imag_profile = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                            $usr->profile->image = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                            $user[$k]->profile = $usr->profile;
                        }
                    } catch (Exception $ef) {
                    }
                }
            } else {

                $user = User::with('profile')->orderBy('id', 'desc')->paginate(5);
                foreach ($user as $k => $usr) {
                    try {
                        if ($usr->profile != null) {

                            $file = ($usr->profile->imag_profile);
                            $usr->profile->imag_profile = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                            $usr->profile->image = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                            $user[$k]->profile = $usr->profile;
                        }
                    } catch (Exception $ef) {
                    }
                }
            }
        } else {
            $user = User::with('profile')->where('id', $id)->first();
            try {
                if ($user->profile != null) {

                    $file = ($user->profile->imag_profile);
                    $user->profile->imag_profile = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                    $user->profile->image = $request->getSchemeAndHttpHost() . '/api/fs/' . $file;
                }
            } catch (Exception $ef) {
            }
        }

        return response()->json($user);
    }

    public function store(Request $request, $id = null)
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
            $email = ($request->has('email')) ? $request->input('email') : Str::random(10) . "@" . Str::random(5) . "com";
            if ($id == null) {


                $user = User::create([
                    "first_name" => $request->input('first_name'),
                    "last_name" => $request->input('last_name'),
                    "description" => $request->input('description'),
                    "email" => $email,
                    "password" => ($request->has('password')) ? $request->input('password') : Str::random(10)
                ]);

                $profile = User::find($user->id);
                $asso = new Profile([
                    "imag_profile" => $filePath
                ]);
                $profile->profile()->save($asso);


            } else {
                if (User::where('id', $id)->count() <= 0) {
                    throw new Exception('data no found');
                }

                $user = User::find($id);
                $user->first_name = $request->input('first_name');
                $user->last_name = $request->input('last_name');
                $user->description = $request->input('description');

                if ($request->has('password')) {
                    $user->password = bcrypt($request->input('password'));
                }

                if ($request->has('email')) {
                    $user->email = $email;
                }

                $user->save();

                if ($request->hasFile('imag_profile')) {
                    Profile::where('user_id', $id)->update([
                        "imag_profile" => $filePath
                    ]);
                }
            }

            return response()->json($user);
        } catch (Exception $e) {
            $data = ['message' => $e->getMessage()];
            return response()->json($data, 400);
        }
    }

    public function delete($id)
    {
        try {

            if (User::where('id', $id)->count() <= 0) {
                throw new Exception('data no found');
            }

            $user = User::find($id);
            $profile = Profile::where('user_id', $id)->count();
            if ($profile > 0) {
                $first = Profile::where('user_id', $id)->first();

                Storage::disk('public')->delete($first->imag_profile);

                Profile::where('user_id', $id)->delete();
                $user->delete();
            } else {
                $user->delete();
            }
//            return response()->json($user);
            return response()->noContent();
        } catch (Exception $e) {
            $data = ['message' => $e->getMessage()];
            return response()->json($data, 400);
        }
    }

    public function getPubliclyStorageFile($filename)

    {
//        $path = storage_path('app/public/'. $filename);
        $path = storage_path('app/public/images/' . $filename);

        if (!\File::exists($path)) {
            abort(404);
        }

        $file = \File::get($path);
        $type = \File::mimeType($path);

        $response = \Response::make($file, 200);

        $response->header("Content-Type", $type);

        return $response;

    }

}
