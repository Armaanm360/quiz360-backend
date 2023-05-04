<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calculation;
use App\Models\UserForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{

    public function getQuiz($subjective_quiz_id,$userid)
    {
        
        
            $quizes = DB::table('subjective_quiz_table')->where('subjective_quiz_id', $subjective_quiz_id)->get();
            unset($quizes->quiz_code);
            $table_get = $quizes[0]->question_table;
            $table_get_answer = $quizes[0]->answer_table;
            $all_questions = DB::table($table_get)->get();


            /* Check if user exist */
             $check = DB::table($table_get_answer)->where('answerd_by', $userid)->count();
             if ($check == 0) {
            return response()->json([
                'data' =>  $all_questions,
                'success' => true
            ], 200);
             }else{
            $getGrad     = new Calculation();
            $getusername = new UserForm();
            $user_info = DB::table($table_get_answer)->where('answerd_by', $userid)->first();
            $grad        = $getGrad->getGrad($user_info->total_marks, $quizes[0]->quiz_number);
            $fastest = DB::table($table_get_answer)->orderBy('takentime', 'ASC')->WhereNotNull('takentime')->where('takentime', '!=', 0)->get();
            $fastest_answer = $fastest[0]->takentime;




            $getans = [
                'takentime'      => $user_info->takentime,
                'answerd_by'     => $userid,
                'right_ans'      => $user_info->right_ans,
                'wrong_ans'      => $user_info->wrong_ans,
                'skipped_ans'    => $user_info->skipped_ans,
                'total_marks'    => $user_info->total_marks,
                'grad'           => $grad,
                'fastest' => $fastest_answer,
                'subjective_quiz_id' => $subjective_quiz_id,
                'username' => $getusername->getusername($userid),
                'quizname' => $quizes[0]->subjective_quiz_name,
            ];
            return response()->json([
                
                'data' =>  $getans,
                'success' => false
            ], 200);

             }


        

    }



    public function getAllQuizes($userid = '')
    {
        
        $quizes = DB::table('subjective_quiz_table')
        ->select( 'subjective_quiz_table.subjective_quiz_id','subjective_quiz_table.subjective_quiz_name','users.name', 'subjective_quiz_table.quiz_code', 'subjective_quiz_table.quiz_number', 'subjective_quiz_table.quiz_time', 'college_subject.college_subject_name')
        ->join('college_subject', 'college_subject.college_sub_id','=', 'subjective_quiz_table.subjective_sub_id')
        ->join('users','users.id','=','subjective_quiz_table.quiz_creator_id')
        ->get();
        return response()->json([
            'data' =>  $quizes,
            'success' => true
        ], 200);
    }



    public function getUserAnswer(Request $request)
    {


        if ($request->ismethod('post')) {
            $user_answer = $request->all();
            $getGrad     = new Calculation();
            $getusername = new UserForm();
            $answerd_by  = $request->user_id;
            $takentime   = $request->countquiztime;
            $username    = $getusername->getUserName($request->user_id);

            $user_answer_slice = array_slice($user_answer, 1);
            unset($user_answer_slice['get_subjective_id']);
            unset($user_answer_slice['countquiztime']);


            $subjective_quiz_id = $request->get_subjective_id;

            $table_info = DB::table('subjective_quiz_table')->where('subjective_quiz_id', $subjective_quiz_id)->get();
            $quizname = $table_info[0]->subjective_quiz_name;
            $question_table = $table_info[0]->question_table;
            $answer_table = $table_info[0]->answer_table;
            $get_type     = $table_info[0]->quiz_type;
            $quiz_time     = $table_info[0]->quiz_time;
            $row_count = DB::table($question_table)->count();

            $range = range(1, $row_count);


            $get_number = (object)$range;
            $i = $get_number;
            foreach ($i as $icount) {
                $em = 'quiz_' . $icount;
                $dem = 'ans_' . $icount;
                $data[$dem] = $request->$em;
            }

            $answers = DB::table($question_table)->select('quiz_answer')->get();
            $json = json_encode($answers);
            $array = json_decode($json, true);



            // echo '<pre>';
            // print_r($user_answer);die;
            // $user_answer_slice = array_slice($user_answer, 1);
            // unset($user_answer_slice['get_subjective_id']);
            // unset($user_answer_slice['countquiztime']);


            $data['answerd_by'] = $answerd_by;

            $array_user = array_values($user_answer_slice);
            $array_quiz = array_column($array, 'quiz_answer');
            $wrong_answers = count(array_diff($array_user, $array_quiz));
            $answerd = count($user_answer_slice);

            $data['skipped_ans'] = $row_count - $answerd;
            $data['right_ans'] = $answerd - $wrong_answers;
            $data['wrong_ans'] = $wrong_answers;
            $data['total_marks'] = $data['right_ans'] * 1;
            $data['takentime'] = $takentime;


            $grad = $getGrad->getGrad($data['total_marks'], $row_count);



            DB::table($answer_table)->insert($data);


            $getmarks = DB::table($answer_table)->where('answerd_by', $answerd_by)->get();
            $fastest = DB::table($answer_table)->orderBy('takentime', 'ASC')->WhereNotNull('takentime')->where('takentime', '!=',0)->get();
            $fastest_answer = $fastest[0]->takentime;






            $getans = array();
            $getans = [
                'takentime'      => $takentime,
                'answerd_by'     => $answerd_by,
                'right_ans'      => $data['right_ans'],
                'wrong_ans'      => $data['wrong_ans'],
                'skipped_ans'    => $data['skipped_ans'],
                'total_marks'    => $data['total_marks'],
                'grad'           => $grad,
                'fastest' => $fastest_answer,
                'subjective_quiz_id' => $subjective_quiz_id,
                'username' => $username,
                'quizname' => $quizname,
            ];



            return response()->json([
                'data' =>  $getans,
                'success' => true
            ], 200);


        }
       




        
        
        
        



            


        
    }


    /*  */


    /* Trending Now */

    public function trendingNow()
    {
        $quizes = DB::table('subjective_quiz_table')
        ->select('subjective_quiz_table.subjective_quiz_id', 'subjective_quiz_table.subjective_quiz_name', 'users.name', 'subjective_quiz_table.quiz_code', 'subjective_quiz_table.quiz_number', 'subjective_quiz_table.quiz_time', 'college_subject.college_subject_name','subjective_quiz_table.quiz_type','users.image')
        ->join('college_subject', 'college_subject.college_sub_id', '=', 'subjective_quiz_table.subjective_sub_id')
        ->join('users', 'users.id', '=', 'subjective_quiz_table.quiz_creator_id')
        ->get();
        return response()->json([
            'data' =>  $quizes,
            'success' => true
        ], 200);
       
    }
    
    
   public function getCreators(){
       
      $getCreators = DB::table('user_type')->select('users.id','user_type.user_status','users.name')->where('get_user_type','quiz_creator')->join('users','users.id','user_type.sl_user_id')->get();
             return response()->json([
            'data' =>  $getCreators,
            'success' => true
        ], 200);
   
       
   }   
   
   
   public function activeCreator($activeCreator){
       
      $getCreators = DB::table('user_type')->where('sl_user_id',$activeCreator)->update([
            'user_status' => 1
          ]);
             return response()->json([
            'success' => true,
            'msg'     => 'status activated'
        ], 200);
   
       
   }
   
   

    public function topPicks()
    {
        $quizes = DB::table('subjective_quiz_table')
        ->where('quiz_status','=',NULL)->select('subjective_quiz_table.subjective_quiz_id', 'subjective_quiz_table.subjective_quiz_name','subjective_quiz_table.quiz_type','users.name', 'subjective_quiz_table.quiz_code', 'subjective_quiz_table.quiz_number', 'subjective_quiz_table.quiz_time','college_subject.college_subject_name','users.image')
        ->join('college_subject', 'college_subject.college_sub_id', '=', 'subjective_quiz_table.subjective_sub_id')
        ->join('users', 'users.id', '=', 'subjective_quiz_table.quiz_creator_id')
        ->get();
        return response()->json([
            'data' =>  $quizes,
            'success' => true
        ], 200);
       
    }

     public function archived()
    {
        $quizes = DB::table('subjective_quiz_table')
        ->where('quiz_status','archeived')->select('subjective_quiz_table.subjective_quiz_id', 'subjective_quiz_table.subjective_quiz_name','subjective_quiz_table.quiz_type','users.name', 'subjective_quiz_table.quiz_code', 'subjective_quiz_table.quiz_number', 'subjective_quiz_table.quiz_time','college_subject.college_subject_name','users.image')
        ->join('college_subject', 'college_subject.college_sub_id', '=', 'subjective_quiz_table.subjective_sub_id')
        ->join('users', 'users.id', '=', 'subjective_quiz_table.quiz_creator_id')
        ->get();
        return response()->json([
            'data' =>  $quizes,
            'success' => true
        ], 200);
       
    }
    
    
  public function upcoming()
    {
        $quizes = DB::table('subjective_quiz_table')
        ->where('quiz_status','upcoming')->select('subjective_quiz_table.subjective_quiz_id', 'subjective_quiz_table.subjective_quiz_name','subjective_quiz_table.quiz_type','users.name', 'subjective_quiz_table.quiz_code', 'subjective_quiz_table.quiz_number', 'subjective_quiz_table.quiz_time','college_subject.college_subject_name','users.image')
        ->join('college_subject', 'college_subject.college_sub_id', '=', 'subjective_quiz_table.subjective_sub_id')
        ->join('users', 'users.id', '=', 'subjective_quiz_table.quiz_creator_id')
        ->get();
        return response()->json([
            'data' =>  $quizes,
            'success' => true
        ], 200);
       
    }

    public function starsWeek()
    {

        $answer_table = DB::table('subjective_quiz_table')->orderBy('subjective_quiz_id', 'DESC')->get();

        $top3 =  DB::table($answer_table[0]->answer_table)->orderBy('total_marks','DESC')->orderBy('takentime','ASC')->select(
            $answer_table[0]->answer_table . '.' . 'answerd_by', 
            $answer_table[0]->answer_table . '.' . 'total_marks',
            $answer_table[0]->answer_table . '.' . 'right_ans', 
            $answer_table[0]->answer_table . '.' . 'right_ans',
            $answer_table[0]->answer_table . '.' . 'takentime',
            $answer_table[0]->answer_table . '.' .'wrong_ans','users.name','users.image')->join('users','users.id','=', $answer_table[0]->answer_table.'.'.'answerd_by')->take(3)->get();

        return response()->json([
            'data'    => $top3,
            'success' => true,
            'quiz_name' => $answer_table[0]->subjective_quiz_name
        ]);

       
    }

    public function topCate()
    {
        $cate = DB::table('college_subject')->select('college_sub_id', 'college_subject_name', 'college_subject_desc')->get();

        return response()->json([
            'data' => $cate,
            'success' => true
        ],200);
    }


    public function topSponsers()
    {
        $sponsers = DB::table('subjective_quiz_table')->select('subjective_quiz_table.quiz_creator_id','users.name','users.image')->join('users','users.id','=', 'subjective_quiz_table.quiz_creator_id')->groupBy('users.name')->get();

        return response()->json([
            'data' => $sponsers,
            'success' => true
        ], 200);
    }

    public function topLeads($quizid)
    {
          

        $answer_table = DB::table('subjective_quiz_table')->where('subjective_quiz_id',$quizid)->orderBy('subjective_quiz_id', 'DESC')->get();


        $top3 =  DB::table($answer_table[0]->answer_table)->orderBy('total_marks', 'DESC')->orderBy('takentime', 'ASC')->select(
            $answer_table[0]->answer_table . '.' . 'answerd_by',
            $answer_table[0]->answer_table . '.' . 'total_marks',
            $answer_table[0]->answer_table . '.' . 'right_ans',
            $answer_table[0]->answer_table . '.' . 'right_ans',
            $answer_table[0]->answer_table . '.' . 'takentime',
            $answer_table[0]->answer_table . '.' . 'wrong_ans',
            'users.name',
            'users.image',
            'users.phone'
        )->join('users', 'users.id', '=', $answer_table[0]->answer_table . '.' . 'answerd_by')->get();

        return response()->json([
            'data'    => $top3,
            'success' => true,
            'quiz_name' => $answer_table[0]->subjective_quiz_name
        ]);
    }



    public function questionViewPage($quizId)
    {

        $answer_table = DB::table('subjective_quiz_table')->where('subjective_quiz_id', $quizId)->get();
        $question_count = DB::table($answer_table[0]->question_table)->count();
        $counted  = $answer_table[0]->quiz_number;

        if ($question_count == $counted) {
            $count_status = 'completed';
        }else{
            $count_status = 'unfinished';
        }
        //unset($answer_table[0]->quiz_result_hour);
        return response()->json([
            'quizid'    => $quizId,
            'data'     => $answer_table,
            'question_count' => $question_count,
            'complete'       => $count_status
        ]);
        
    }

    
    
}