<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;



class AuthController extends Controller
{
    //

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'error' =>  $validator->messages()->first()
                ],
                400
            );
        }

        $credentials = $request->only('email', 'password');
        $token =
            JWTAuth::attempt($credentials);

        // authentication failed
        if (!$token) {
            return response()->json([
                'message' => 'Incorrect email or password, try again.'
            ], 401);
        }

        $userResponse = getUser($request->email);
        $userResponse->token = $token;
        $userResponse->token_expires_in = JWTAuth::factory()->getTTL() * 60;
        $userResponse->token_type = 'bearer';
        // success authentication
        return response()->json($userResponse, 200);
    }

    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|numeric',
            'pin' => 'required|digits:6',
            "nim" => 'nullable|digits:10|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => $validator->messages()->first(),
                ]
            ], 422);
        }
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $user = User::where('email', $request->email)->exists();

        if ($user) {
            return response()->json(['message' => 'Email already taken'], 409);
        }

        DB::beginTransaction();

        try {
            $profilePicture = null;
            if ($request->profile_picture) {
                $profilePicture = uploadBase64Image($request->profile_picture);
            }

            $ktp = null;
            if ($request->ktp) {
                $ktp = uploadBase64Image($request->ktp);
            }
            $role_id = null;
            if ($request->role_id) {
                $role_id = $request->role_id;
            }


            $uuid = Str::uuid();
            $user = User::create([
                'uuid' => $uuid,
                'nim' => $request->nim,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $role_id,
                'profile_picture' => $profilePicture,
                'ktp' => $ktp,
                'verified' => ($ktp) ? true : false
            ]);

            // success condition
            // if ($user) {
            //     return response()->json([
            //         'error' => false,
            //         'data' => [
            //             'message' => "Success create user."
            //         ]
            //     ], 200);
            // }

            // // false condition
            // return response()->json([
            //     'error' => true,
            //     'data' => [
            //         'message' => "Failed create user, try again."
            //     ]
            // ], 302);

            $cardNumber = $this->generateCardNumber(16);

            Wallet::create([
                'user_id' => $user->uuid,
                'balance' => 0,
                'pin' => $request->pin,
                'card_number' => $cardNumber
            ]);

            DB::commit();
            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);

            $userResponse = getUser($user->uuid);
            $userResponse->token = $token;
            $userResponse->token_expires_in = JWTAuth::factory()->getTTL() * 60;
            $userResponse->token_type = 'bearer';

            return response()->json($userResponse);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function myprofile()
    {
        $user = auth('api')->user();
        return response()->json([
            'error' => false,
            'data' => [
                'user' => [
                    'uuid' => $user->uuid,
                    'nim' => $user->nim,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name
                ]
            ]
        ], 200);
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        if ($removeToken) {
            return response()->json([
                'error' => false,
                'data' => [
                    'message' => "Logout success, invalidate the token."
                ]
            ]);
        }
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    private function generateCardNumber($length)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        $wallet = Wallet::where('card_number', $result)->exists();
        if ($wallet) {
            return $this->generateCardNumber($length);
        }
        return $result;
    }

    // private function uploadBase64Image($base64Image)
    // {
    //     $decoder = new Base64ImageDecoder($base64Image, $allowedFormats = ['jpeg', 'png', 'jpg']);

    //     $decodedContent = $decoder->getDecodedContent();
    //     $format = $decoder->getFormat();
    //     $image = Str::random(10) . "." . $format;
    //     Storage::disk('public')->put($image, $decodedContent);

    //     return $image;
    // }

}
