<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Rules\CheckNumberRule;

class userRegisterController extends Controller
{
    public function userRegisterController(Request $request)
    {
        $is_api_request = Request()->route()->getPrefix() === 'api';
        if ($is_api_request) {
            if ($request->ismethod('post')) {
                $data = $request->all();


                $rules = [
                    'name' => 'required',
                    'phone' => 'required|min:11|unique:users',
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
                $user->phone = $data['phone'];
                $user->password = bcrypt($data['password']);
                $user->save();



                // $check_number_get = DB::table('check_user_table')->where('user_number',$request->phone)->count();


                // if($check_number_get == 1){
                //               $check_number_get = DB::table('check_user_table')->where('user_number',$request->phone)->update(['perticipant_id' => $user->id]);
                //               $check_number_get_sub_id = DB::table('check_user_table')->where('user_number',$request->phone)->first();
                //               $check_sub = $check_number_get_sub_id->sub_id;
                //               $check_certified_user = 'varified';
                // }else{
                //     $check_certified_user = 'not-varified';
                //     $check_sub = 0;
                // }



                DB::table('user_type')->insert(['get_user_type' => NULL, 'sl_user_id' => $user->id, 'user_status' => false]);


                /* passport */

                $user_type_get = DB::table('user_type')->where('sl_user_id', $user->id)->get();

                if (Auth::attempt(['phone' => $data['phone'], 'password' => $data['password']])) {
                    $user = User::where('phone', $data['phone'])->first();
                    $access_token = $user->createToken($data['phone'])->accessToken;
                    User::where('phone', $data['phone'])->update(['access_token' => $access_token]);
                    $message = 'User Successfully Registerd';

                    $check_number_get = DB::table('check_user_table')->where('user_number', $request->phone)->count();


                    if ($check_number_get == 1) {
                        $check_number_get = DB::table('check_user_table')->where('user_number', $request->phone)->update(['perticipant_id' => $user->id]);
                        $check_number_get_sub_id = DB::table('check_user_table')->where('user_number', $request->phone)->first();
                        $check_sub = $check_number_get_sub_id->sub_id;
                        $check_certified_user = 'varified';
                    } else {
                        $check_certified_user = 'not-varified';
                        $check_sub = 0;
                    }
                    return response()->json(['message' => $message, 'access_token' => $access_token, 'success' => true, 'user_type' => NULL, 'is_checked' => $check_certified_user, 'approved_sub_id' => $check_sub, 'is_varified' => $user_type_get[0]->user_status], 201);
                } else {
                    $message = 'Oppss Something Went Wrong';
                    return response()->json(['message' => $message, 'success' => false], 422);
                }
            }
        } else {
            $user = new User();
            $user->name = $data['name'];
            $user->phone = $data['phone'];
            $user->password = bcrypt($data['password']);
            $user->save();

            if (Auth::attempt(['phone' => $data['phone'], 'password' => $data['password']])) {
                $user = User::where('phone', $data['phone'])->first();
                $access_token = $user->createToken($data['phone'])->accessToken;
                User::where('phone', $data['phone'])->update(['access_token' => $access_token]);
            }
        }
    }



    public function userRegisterShow()
    {
        return view('admin.pages.quiz.meow');
    }

    public function userLoginController(Request $request)
    {
        if ($request->ismethod('post')) {
            $data = $request->all();

            $rules = [
                'phone' => 'required|exists:users',
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


            if (Auth::attempt(['phone' => $data['phone'], 'password' => $data['password']])) {
                $user = User::join('user_type', 'user_type.sl_user_id', '=', 'users.id')->where('phone', $data['phone'])->first();
                unset($user['facebook_id'], $user['access_token'], $user['google_id'], $user['google_id'], $user['designation'], $user['institution_id'], $user['description'], $user['slug'], $user['email_verified_at'], $user['ministry_department'], $user['organization_user'], $user['regulatory_authority'], $user['branch_user'], $user['signature'], $user['division'], $user['role'], $user['user_creator'], $user['user_category']);



                $access_token = $user->createToken($data['phone'])->accessToken;
                User::where('phone', $data['phone'])->update(['access_token' => $access_token]);


                $message = 'User Successfully Login';




                $user_type = DB::table('user_type')->where('sl_user_id', $user['id'])->get();
                $user_approval = DB::table('user_type')->where('sl_user_id', $user['id'])->get();



                $check_number_get_sub_id = DB::table('check_user_table')->where('user_number', $user['phone'])->count();
                // $check_number_get_subject = DB::table('check_user_table')->where('user_number',$user['phone'])->first();
                $check_number_get_subject = DB::table('check_user_table')->where('perticipant_id', $user['id'])->first();

                if ($check_number_get_sub_id > 0) {
                    $test = DB::table('check_user_table')->where('user_number', $data['phone'])->update(['perticipant_id' => $user['id']]);
                    $is_varified = 'varified';
                    $sub_id = $check_number_get_subject->sub_id;
                } else {
                    $is_varified = 'not varified';
                    $sub_id = 0;
                }


                // if($check_number_get_sub_id > 0){
                //                   $check_number_get_sub_id = DB::table('check_user_table')->where('perticipant_id',$user['id'])->first();
                //                   $test = DB::table('check_user_table')->where('user_number',$data['phone'])->update(['perticipant_id'=>$user['id']]);
                //                   $is_varified = 'varified';
                //                   $sub_id = $check_number_get_sub_id->sub_id;
                // }else{
                //  $is_varified = 'not varified';
                //  $sub_id = 0;
                // }
                return response()->json([
                    'message' => $message,
                    'access_token' => $access_token,
                    'sub_id' => $sub_id,
                    'is_varified' => $is_varified,
                    'data' =>  $user,
                    'success' => true,
                    'user_count' => $check_number_get_sub_id
                ], 201);
            } else {
                $message = 'Ooops Something Went Wrong';
                return response()->json(['message' => $message, 'success' => false], 422);
            }
        }
    }


    public function sendSmsProcess($mobile_number)
    {

        $check_first = DB::table('otp_table')->where('otp_user_number', $mobile_number)->count();


        if ($check_first > 0) {
            return response()->json([
                'success' => false,
                'msg'     => 'you have been already registerd'
            ]);
        } else {

            $curl = curl_init();
            //sent otp  
            $digits = 6;
            $otp = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

            //insert to table
            $check_second =  DB::table('otp_table')->insert([
                'otp_user_number' => '88' .

                    $mobile_number,
                'otp_random_code' => $otp,
            ]);


            $phone_number = '88' . $mobile_number;

            // echo $phone_number;
            // die;

            // $senderId = "8809638336699";
            // $Is_Unicode = false;
            // $Is_Flash = false;
            // $smsTo = $mobile_number;
            // $smsMessage = "Quiz360%20Verification%20Code%20is%20.' '.$otp";
            // $apiKey = "Cvgct1ut70BRpXaNJfDT%2F%2BX7RDUtyQ5NWS8MrFwgcyI%3D";
            // $clientId = "a9d4b3b7-4363-4c81-9408-6c14c01eb386";

            // curl_setopt_array($curl, array(
            //     CURLOPT_URL =>  "http://36.255.69.155/api/v2/SendSMS" .
            //         "?SenderId=" . $senderId .
            //         "&Is_Unicode=" . $Is_Unicode .
            //         "&Is_Flash=" . $Is_Flash .
            //         "&Message=" . $smsMessage .
            //         "&MobileNumbers=" . $smsTo .
            //         "&ApiKey=" . $apiKey .
            //         "&ClientId=" . $clientId,
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HEADER => false,
            //     CURLOPT_SSL_VERIFYPEER => false,
            //     CURLOPT_SSL_VERIFYHOST => false
            // ));

            // $response = curl_exec($curl);
            // $err = curl_error($curl);

            // curl_close($curl);

            // if ($err) {
            //     echo "cURL Error #:" . $err;
            // } else {
            //     echo $response;
            // }

            // $new_centername = '0052151';
            // $address = 'no_address';
            // $center_mobile = '8801401033443';

            // $message = urlencode("Scheduled at " . $new_centername . " and " . $address . " and " . $center_mobile);
            // $apiURL = "http://103.16.101.52:8080/bulksms/bulksms?username=abc-def&password=abc123&type=0&dlr=1&destination=" . $conturyCode . $client_mobile . "&source=ABC&message=" . $message;


            // $apiURL = "http://36.255.69.155/api/v2/SendSMS?ApiKey={ApiKey}&ClientId={ClientId}&SenderId={SenderId}&Message={Message}&MobileNumbers={MobileNumbers}&Is_Unicode={Is_Unicode}&Is_Flash={Is_Flash}";





            // $apiURL = "http://36.255.69.155/api/v2/SendSMS?SenderId=8809638336699&Is_Unicode=false&Is_Flash=false&Message=Quiz360%20varification%20Code%20is%20' . $otp . '&MobileNumbers=' . $center_mobile . '&ApiKey=Cvgct1ut70BRpXaNJfDT/+X7RDUtyQ5NWS8MrFwgcyI=&ClientId=a9d4b3b7-4363-4c81-9408-6c14c01eb386";



            // $apiURL = "http://36.255.69.155/api/v2/SendSMS?SenderId=8809638336699&Is_Unicode=false&Is_Flash=false&Message=Quiz360%20Verification%20Code%20is%20a-4506580&MobileNumbers=8801401033443&ApiKey=Cvgct1ut70BRpXaNJfDT%2F%2BX7RDUtyQ5NWS8MrFwgcyI%3D&ClientId=a9d4b3b7-4363-4c81-9408-6c14c01eb386";

            // $Curl_Session = curl_init('http://36.255.69.155/api/v2/SendSMS?SenderId=8809638336699&Is_Unicode=false&Is_Flash=false&Message=Quiz360%20Verification%20Code%20is%20a-4506580&MobileNumbers=8801401033443&ApiKey=Cvgct1ut70BRpXaNJfDT%2F%2BX7RDUtyQ5NWS8MrFwgcyI%3D&ClientId=a9d4b3b7-4363-4c81-9408-6c14c01eb386');
            // curl_setopt($Curl_Session, CURLOPT_POST, 1);
            // curl_setopt($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
            // curl_setopt($Curl_Session, CURLOPT_RETURNTRANSFER, 1);
            // $result = curl_exec($Curl_Session);



            $url = 'http://36.255.69.155/api/v2/SendSMS?SenderId=8809638336699&Is_Unicode=false&Is_Flash=false&Message=Quiz360%20Verification%20Code%20is%20' . $otp . '&MobileNumbers=' . $phone_number . '&ApiKey=Cvgct1ut70BRpXaNJfDT%2F%2BX7RDUtyQ5NWS8MrFwgcyI%3D&ClientId=a9d4b3b7-4363-4c81-9408-6c14c01eb386';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);



            $message = 'we have sent a otp in your number';
            return response()->json([
                'message' => $message,
                'success' => true,
                'resss' => $response,
                'cunt' => $check_first

            ], 200);
        }
    }


    public function userUpgradation($userId)
    {
        $get_user = DB::table('users')->where('id', $userId)->where('user_type', '=', NULL)->update([
            'user_type' => 'participant',

        ]);

        $get_user_type = DB::table('user_type')->where('sl_user_id', $userId)->update(['get_user_type' => 'participant', 'user_status' =>  true]);

        $participant = array();

        $participant = [
            'user_id' => $userId,
            'user_type' => 'participant'
        ];

        return response()->json([
            'success' => true,
            'data'    => $participant

        ], 200);
    }


    public function userUpgradationCreator($userId)
    {
        $get_user = DB::table('users')->where('id', $userId)->where('user_type', '=', NULL)->update([
            'user_type' => 'quiz_creator',
            'status' => false,

        ]);

        $get_user_type = DB::table('user_type')->where('sl_user_id', $userId)->update(['get_user_type' => 'quiz_creator', 'user_status' =>  false]);

        $participant = array();

        $participant = [
            'user_id' => $userId,
            'user_type' => 'quiz_creator',
            'user_status' => false
        ];

        return response()->json([
            'success' => true,
            'data'    => $participant

        ], 200);
    }


    public function creatorInformation(Request $request)
    {
        if ($request->hasFile(['logo'])) {
            $file = $request->file('logo');
            $filename = $file->getClientOriginalExtension();
            $logo   = rand() . \date('His') . '.' . $filename;
            $file->move(\public_path('institution_logo'), $logo);
            $credential = $request->file('credential');
            $credential_name = $credential->getClientOriginalExtension();
            $credential_picture   = rand() . \date('His') . '.' . $credential_name;
            $credential->move(\public_path('quiz_creators'), $credential_picture);
            $userid = $request->userid;
            $institution_name = $request->institution_name;

            DB::table('user_type')->where('sl_user_id', $userid)->update([
                'credential' => $credential_picture,
                'institution_name' => $institution_name,
                'user_status'    => false
            ]);

            DB::table('users')->where('id', $userid)->where('user_type', 'quiz_creator')->update([
                'image' => $logo,
                'status' => true
            ]);


            $join_info = DB::table('users')->where('id', $request->user_id)->join('user_type', 'user_type.sl_user_id', '=', 'users.id')->get();

            $info = array();
            $info = [
                'institution_name' => $request->institution_name,
                'get_user_type'    => 'quiz_creator',
                'logo'             => $logo,
                'user_id'          => $userid
            ];

            return response()->json([
                'success' => true,
                'file' => $credential_picture,
                'data' => $info
            ]);
        } else {
            return response()->json(['success' => false, 'error' => 'Image Not Uploaded Properly']);
        }
    }
}
