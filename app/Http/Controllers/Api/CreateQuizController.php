<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateQuizController extends Controller
{

    public function showSubjectQuiz()
    {
        $sub = DB::table('college_subject')->get();
        return response()->json([
            'data' => $sub,
            'success' => true
        ]);
    }

    public function createQuizPost(Request $request)
    {


        $sub_id = $request->subjective_sub_id;
        $table_name = $request->table_name_prefix;
        $sub_name   = substr($request->quiz_name, 0, 3);
        $qui_code   = $request->quiz_code;
        $qui_number   = $request->quiz_number;
        $question_table = strtolower($table_name . '_' . $sub_name . '_' . $qui_code . '_' . $qui_number . '_' . 'question');
        $answer_table = strtolower($table_name . '_' . $sub_name . '_' . $qui_code . '_' . $qui_number . '_' . 'answer');


        $createTableSqlString = "CREATE TABLE $question_table (
    quiz_id INT AUTO_INCREMENT primary key NOT NULL,
    quiz_code varchar(255),
    quiz_sub_name varchar(255),
    quiz_sub_id varchar(255),
    quiz_question LONGTEXT,
    quiz_option_1 LONGTEXT,
    quiz_option_2 LONGTEXT,
    quiz_option_3 LONGTEXT,
    quiz_option_4 LONGTEXT,
    quiz_answer LONGTEXT,
    quiz_remarks LONGTEXT)";










        $createTableSqlStringAnswer = "CREATE TABLE $answer_table (
    quiz_ans_id INT AUTO_INCREMENT primary key NOT NULL,";

        for ($i = 1; $i <= $qui_number; $i++) {
            $createTableSqlStringAnswer .= " ans_$i LONGTEXT,";
        }

        $createTableSqlStringAnswer .= "takentime FLOAT,answerd_by int(11),right_ans int(11),wrong_ans int(11),skipped_ans int(11),total_marks int(11))";

        DB::statement($createTableSqlString);
        DB::statement($createTableSqlStringAnswer);
        DB::table('subjective_quiz_table')->insert([
            'subjective_sub_id' => $sub_id,
            'quiz_creator_id' => $request->quiz_creator_id,
            'quiz_number' => $request->quiz_number,
            'question_table'    => $question_table,
            'answer_table'      => $answer_table,
            'subjective_quiz_name' => $request->quiz_name,
            'quiz_time'         => $request->quiz_time,
            'quiz_code'         => $request->quiz_code,
            'quiz_type'         => $request->quiz_type,
        ]);


        $info = array();
        $info = [
            'subjective_sub_id' => $sub_id,
            'quiz_creator_id' => $request->quiz_creator_id,
            'quiz_number' => $request->quiz_number,
            'question_table'    => $question_table,
            'answer_table'      => $answer_table,
            'subjective_quiz_name' => $request->quiz_name,
            'quiz_time'         => $request->quiz_time,
            'quiz_code'         => $request->quiz_code,
            'quiz_type'         => $request->quiz_type,
        ];


        return response()->json([
            'success' => true,
            'data'    => $info
        ],200);




    }


    public function showquizAll($userId)
    {
       $data = DB::table('subjective_quiz_table')->where('quiz_creator_id',$userId)->get();
       return response()->json([
        'success' => true,
         'data'   => $data
       ]);
    }


    public function createQuestion(Request $request)
    {
        $subjective_quiz_id = $request->subjective_quiz_id;
        $table_info = DB::table('subjective_quiz_table')->where('subjective_quiz_id', $subjective_quiz_id)->get();
        $question_table = $table_info[0]->question_table;
        $quiz_number = $table_info[0]->quiz_number;
        $counted = DB::table($question_table)->count();

        $data = $request->all();
        $rules = [
            'quiz_question' => 'required|min:1',
            'quiz_option_1' => 'required',
            'quiz_option_2' => 'required',
            'quiz_option_3' => 'required',
            'quiz_option_4' => 'required',
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

       if ($counted == $quiz_number) {
            $alldata = DB::table($question_table)->get()->take($quiz_number);
            return response()->json([
                'success' => true,
                'question_status' => 'finished',
                'data' => $alldata,
            ]);

        }else{

            $question = DB::table($question_table)->insert([
                'quiz_question' => $request->quiz_question,
                'quiz_option_1' => $request->quiz_option_1,
                'quiz_option_2' => $request->quiz_option_2,
                'quiz_option_3' => $request->quiz_option_3,
                'quiz_option_4' => $request->quiz_option_4,
                'quiz_answer'   => $request->quiz_answer,
                'quiz_remarks'   => $request->quiz_remarks,
            ]);

            $alldata = DB::table($question_table)->get()->take($quiz_number);
            return response()->json([
                'success' => true,
                'data'   => $alldata,
            ]);

        }


        
    }
    
    public function createTodo(Request $request){
        $createTodo = DB::table('todo')->insert([
            'tasks' => $request-> tasks,
            'points' => $request-> points,
            
            ]);
            
             return response()->json([
                'success' => true,
                'data'   => $createTodo,
            ]);
            
            
    }
    
    public function createTodoList(){
        $createTodo = DB::table('todo')->get();
            
             return response()->json($createTodo);
            
            
    }
}
