<?php

use App\Http\Controllers\Payroll\PayrollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('new_accountants',  'Api\accountantContoller@storeAccountants');

Route::get('new_accountants/{phone}',  'Api\accountantContoller@getByAccountantsNumber');




//user transaction
Route::post('user-transaction',  'Api\accountantContoller@userTransaction');




//Create Employee

Route::resource('employee', Employee\EmployeeController::class);

Route::resource('payroll', Payroll\PayrollController::class);

Route::resource('salary', salary\SalaryController::class);



//register
Route::post('user-register', 'UserAuth\UserAuthController@userRegister');

//register
Route::post('user-login', 'UserAuth\UserAuthController@userLogin');
