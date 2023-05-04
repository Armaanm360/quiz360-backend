<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class accountantContoller extends Controller
{
    public function storeAccountants(Request $request)
    {
        DB::table('new_accountants')->insert([
            'name' => $request->name,
            'username' => $request->username,
            'userid' => $request->userid,
            'nid' => $request->nid,
            'accountnumber' => $request->accountnumber,
            'phone_number' => $request->phone_number,
        ]);
    }

    public function getByAccountantsNumber($number)
    {
        $lame =    DB::table('new_accountants')->where('employee_id', $number)->first();

        return response()->json($lame);
    }


    public function userTransaction(Request $request)
    {
        $userTransaction =   DB::table('user_transaction')->insert([
            'recipent_id' => $request->recID,
            'basic' => $request->recbasic,
            'days' => $request->recdays,
            'bonus' => $request->recbonus,
            'tda' => $request->rectda,
            'total' => $request->recotal,
            'transaction_type' => 'CREDIT',
            'transaction_create_date' => date('d-m-y')
        ]);

        $adminTransaction = DB::table('account_table')->insert([
            'admin_transaction_type' => 'DEBIT',
            'transaction_purpose' => 'Salary',
            'total_amount' => $request->recotal,

        ]);
    }
}
