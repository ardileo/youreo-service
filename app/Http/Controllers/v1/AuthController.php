<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ErrorBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signin(Request $request)
    {
        if (!empty($request->by_google)) {
            $by_google = base64_decode($request->by_google);
            $by_google = json_decode($by_google);
            $request->email = $by_google->email;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/tokeninfo?id_token=" . $by_google->google_token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            $output = json_decode($output);
            curl_close($ch);

            if (!isset($output->error)) {
                return $this->sign_google($request);
            }

            $out = [
                "code" => 402,
                "message" => "login gagal",
                "result" => [
                    "error"   => [
                        "action" => "signup",
                    ]
                ]
            ];
            return response()->json($out, $out['code']);
        }

        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'password'  => 'required|min:5'
        ]);

        if ($v->fails()) {
            return [
                "message" => "Login Tidak Lengkap",
                "code"    => 401,
                "result" => [
                    "error"   => [
                        "title" => "Gagal Login",
                        "message" => $v->errors()->first(),
                        "btn_1" => ["text" => "Retry", "action" => "dismiss()"]
                    ]
                ],
            ];
        }

        $email = $request->email;
        $password = $request->password;

        $user = User::where("email", $email)->first();

        if (!$user) {
            $out = $this->error_no_user();
            return response()->json($out, $out['code']);
        }

        if (Hash::check($password, $user->password)) {
            $newtoken  = $this->generateRandomString(50);
            $user->api_token = $newtoken;
            $user->save();

            $request->request->add(['user_for' => "credential"]);
            $user = UserResource::make($user);

            $out = [
                "code" => 200,
                "message" => "login sukses",
                "result" => [
                    "user" => $user,
                ]
            ];
        } else {
            $out = [
                "message" => "kombinasi password & email tidak sesuai",
                "code"    => 401,
            ];
        }

        return response()->json($out, $out['code']);
    }

    public function sign_google(Request $request)
    {
        $email = $request->email;
        $user = User::where("email", $email)->first();

        if (!$user) {

            $out = $this->error_no_user();
            $out['result']['error']['action'] = "signup";
            return response()->json($out, $out['code']);
        }

        $newtoken  = $this->generateRandomString(50);
        $user->api_token = $newtoken;
        $user->save();

        $request->request->add(['user_for' => "credential"]);
        $user = UserResource::make($user);

        $out = [
            "code" => 200,
            "message" => "login sukses",
            "result" => [
                "user" => $user,
            ]
        ];

        return response()->json($out, $out['code']);
    }

    public function signup(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'  => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'username' => 'required|min:5|unique:users',
            'password'  => 'required|min:6',
            'fullname' => 'required',
            'gender' => 'required',
        ]);

        if ($v->fails()) {
            return ErrorBuilder::parse(401, "Data bermasalah, silahkan cek kembali!", [
                "error" => [
                    "title" => "Gagal Login",
                    "message" => $v->errors()->first(),
                    "btn_1" => ["text" => "Check", "action" => "dismiss()"],
                    "btn_2" => ["text" => "Login", "action" => env("MOBILE_APP_HOST") . "logout?act=sign"],
                ]
            ])->toResponse();
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'full_name' => $request->fullname,
            'gender' => $request->gender,
            'address' => $request->address,
            'city' => $request->city,
            'api_token' => $this->generateRandomString(50),
        ]);

        $request->request->add(['get_data' => "all"]);
        $user = UserResource::make($user);
        $out = [
            "code" => 201,
            "message" => "Register sukses",
            "result" => [
                "user" => $user,
            ]
        ];

        return response()->json($out, $out['code']);
    }


    private function error_no_user()
    {
        return [
            "message" => "user not found",
            "code"    => 401,
            "result" => [
                "error" => [
                    "title" => "Akun tidak ditemukan",
                    "message" => "Email atau Password tidak sesuai.",
                    "btn_1" => ["text" => "Daftar", "action" => env("MOBILE_APP_HOST") . "signup"],
                    "btn_2" => ["text" => "Coba akun lain", "action" => "recreate()"]
                ],
            ],
        ];
    }
}
