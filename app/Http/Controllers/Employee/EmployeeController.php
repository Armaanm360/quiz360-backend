<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employee = DB::select('SELECT * FROM viewallemployee');
        //$singleEmployee = DB::select("CALL getSpecificData(".$id.")");

        return response()->json($employee);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $employee = new Employee();
        // $employee->employee_name = $request->employee_name;
        // $employee->postition_name = $request->postition_name;
        // $employee->salary = $request->salary;
        // $employee->address = $request->address;
        // $employee->contact = $request->contact;
        // $employee->status = $request->status;
        // $employee->save();
        // $data =    DB::select('call payruns_employess_store(?)', [$request->employee_name, $request->postition_name, $request->salary, $request->address, $request->contact, $request->status]);


        $newlyArray = array($request->employeeID, $request->paydate, $request->netpay);
        $data =    DB::statement('CALL insertPayroll(?,?,?)', $newlyArray);

        // $data = new CommonResource($employee);

        return response()->json(['success' => true, 'message' => 'Successfully Done', 'data' => $newlyArray], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
