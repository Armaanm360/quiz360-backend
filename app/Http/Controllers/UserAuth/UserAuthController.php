<?php

namespace App\Http\Controllers\UserAuth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    public function userRegister(Request $request)
    {

        if ($request->ismethod('post')) {
            $data = $request->all();


            $rules = [
                'name' => 'required',
                'email' => 'required|min:11|unique:users',
                'password' => 'required|min:6'
            ];

            $validator =  Validator::make($data, $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    $a = array();
                    $a = [
                        'success' => false,
                        'message' => $value[0]
                    ];

                    return response()->json($a);
                    // die;
                }
            }


            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->save();
            if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                $user = User::where('email', $data['email'])->first();
                $access_token = $user->createToken($data['email'])->accessToken;
                User::where('email', $data['email'])->update(['access_token' => $access_token]);
                $message = 'User Successfully Registerd';
                return response()->json(['message' => $message, 'access_token' => $access_token, 'success' => true], 201);
            } else {
                $message = 'Oppss Something Went Wrong';
                return response()->json(['message' => $message, 'success' => false], 422);
            }
        }
    }


    public function userLogin(Request $request)
    {
        if ($request->ismethod('post')) {
            $data = $request->all();

            $rules = [
                'email' => 'required|exists:users',
                'password' => 'required'
            ];

            $validator =  Validator::make($data, $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    $a = array();
                    $a = [
                        'success' => false,
                        'message' => $value[0],
                    ];
                    return response()->json($a);
                }
            }




            /* passport */


            if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                $user = User::where('email', $data['email'])->first();




                $access_token = $user->createToken($data['email'])->accessToken;
                User::where('email', $data['email'])->update(['access_token' => $access_token]);


                $message = 'User Successfully Login';
                return response()->json(['message' => $message, 'success' => true, 'data' => $user], 201);
            } else {
                $message = 'Ooops Something Went Wrong';
                return response()->json(['message' => $message, 'success' => false], 422);
            }
        }
    }
}
