<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseBasicInfo;
use App\Models\CourseLessonBasicInfo; 
use App\Models\Entrollment;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseCategory;
use App\Models\Content;
use App\Models\CourseCompleted;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentResubmit;
use App\Models\AssessmentDetails;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSetting;


use App\Models\ActiveRecallDetails;
use App\Models\ActiveRecallQuestion;


class ActiveRecallController extends Controller
{
    public function addactiverecall()
    {
        $courses = CourseBasicInfo::all();      
        $categories = CourseCategory::all();
        return view('activerecall.addactiverecall', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('courses','categories'));
    }

    
    public function addactiverecallstore(Request $request)
    {
         
        $request->content_id=0;
        $QuizquestionID = ActiveRecallDetails::where('course_category_id',$request->course_category_id)
        ->where('lesson_id',$request->lesson_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id; 

                $QuizDetails = ActiveRecallDetails::findOrFail($quizID);
                $QuizDetails->status = 1;
                $QuizDetails->created_by = Auth::user()->id;
                $QuizDetails->save(); 

        }
        else
        {     

        $count =ActiveRecallDetails::count(); 
 
        
                $QuizDetails = new ActiveRecallDetails();
                $QuizDetails->course_id = $request->course_id;
                $QuizDetails->lesson_id = $request->lesson_id;
                $QuizDetails->course_category_id = $request->course_category_id;
                $QuizDetails->status = 1;
                $QuizDetails->created_by = Auth::user()->id;
                $QuizDetails->save();

            $quizID = $QuizDetails->id;
        }
        
        
        return redirect()->route('manageactiverecall',$quizID)
        ->with('success', 'Assessment created successfully.');
    }


    public function activerecalllist()
    {
        $assessments = ActiveRecallDetails::with(['courseBasicInfo','courseLessonBasicInfo'])->get();
        //$assessments = AssessmentDetails::join('course_basic_infos', 'assessment_details.course_id', '=', 'course_basic_infos.id')->get();
        return view('activerecall.activerecalllist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('assessments'));
    }

    public function activerecallsettinglist()
    {
          
        $categories = CourseCategory::all();
        $assessments = AssessmentSetting::with(['courseBasicInfo','courseLessonBasicInfo'])->get();
        //$assessments = AssessmentDetails::join('course_basic_infos', 'assessment_details.course_id', '=', 'course_basic_infos.id')->get();
        return view('activerecall.activerecallsettinglist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('assessments','categories'));
    }

    public function addactiverecallsetting(Request $request)
    {
        $QuizquestionID = AssessmentSetting::where('course_category_id',$request->course_category_id)
        ->where('lesson_id',$request->lession_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id;
            
            return redirect()->route('activerecallsettinglist')
            ->with('error', 'Assessment setting already created, Please check.');
        }
        else
        {     

        $count =AssessmentSetting::count();
        $question_id = "Assessment".$count;
        
        $QuizDetails = new AssessmentSetting();
        $QuizDetails->course_category_id = $request->course_category_id;
        $QuizDetails->course_id = $request->course_id;
        $QuizDetails->lesson_id = $request->lession_id;
        $QuizDetails->section1_question = $request->section1_question;
        $QuizDetails->section1_mark = $request->section1_mark;
        $QuizDetails->section1_total = $request->section1_total;
        $QuizDetails->section2_question = $request->section2_question;
        $QuizDetails->section2_mark = $request->section2_mark;
        $QuizDetails->section2_total = $request->section2_total;
        $QuizDetails->status = 1;
        $QuizDetails->created_by = Auth::user()->id;
        $QuizDetails->save();

        $quizID = $QuizDetails->id;
        } 
        
        return redirect()->route('activerecallsettinglist')
        ->with('success', 'Assessment Setting created successfully.');
    }

    public function editactiverecallsetting($id)
    {
        $quizQuestion = AssessmentSetting::find($id);
        $quizID = $quizQuestion->quiz_id;
        $quizInfo = AssessmentSetting::with(['courseBasicInfo','courseLessonBasicInfo'])->where('id',$id)->first();
        //$quizQuestion = '';
                    
        return view('activerecall.editactiverecallsetting', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','quizQuestion','quizID'));
    }

    public function deleteactiverecallsetting($id)
    {
        try {
            $quizQuestion = AssessmentSetting::find($id);
             
            $quizID = $quizQuestion->quiz_id;
            if ($quizQuestion) {
                $quizQuestion->delete();
            }
        } catch (\Exception $e) {
        
            return redirect()->route('activerecallsettinglist',$quizID)->with('error', 'Assessment Setting not able to Delete, Try Again');
    
        }
        
        return redirect()->route('activerecallsettinglist',$quizID)->with('success', 'Assessment Setting Deleted Successfully'); 
    }

    public function updateactiverecallsetting(Request $request)
    {
        $id = $request->input('id');
        $quizID = $request->input('quiz_id');
        $batch = AssessmentSetting::find($request->id);
        if ($batch) {
            $batch->update([
                'section1_question' => $request->input('section1_question'),
                'section1_mark' =>  $request->input('section1_mark'),
                'section1_total' => $request->input('section1_total'),
                'section2_question' => $request->input('section2_question'),
                'section2_mark' =>  $request->input('section2_mark'),
                'section2_total' => $request->input('section2_total'),
            ]);
        }
         
        return redirect()->route('activerecallsettinglist',$quizID)->with('success', 'Updated Assessment Setting Successfully');
    }


    public function myActiveRecall()
    {
        $my_course_ids = Entrollment::where('user_id',Auth::user()->id)->where('enrolled','1')->pluck('course_id')->toArray();
        //print_r($my_course_ids);exit;
        $assessments = ActiveRecallDetails::with(['courseBasicInfo','courseLessonBasicInfo','activerecallQuestions'])->whereIn('course_id',$my_course_ids)->where('status',true)->get();
        /*
        $assessments = AssessmentDetails::with(['courseBasicInfo','courseLessonBasicInfo','assessmentQuestions'])
        ->whereHas('courseBasicInfo', function ($query) use($my_course_ids){
            $query->whereIn('id', $my_course_ids);
        })
        ->where('status',true)->get();
        print_r($assessments);exit;
        */

        //$assessments = AssessmentDetails::select('course_id')->where('status',1)->get();
        //$assessments = $assessments->unique();


        $assessmentsArray=[];
        $courseArray=[];
        $unitArray=[];
        foreach($assessments as $assessment){ 
            $courseId = $assessment->courseBasicInfo->id;

            if (in_array($courseId, $courseArray, $strict = true)) {

            } else {                
            $courseArray[]=$assessment->courseBasicInfo->id;
            
            $assessment_course_percentage = $assessment->courseBasicInfo->calculateCompletionPercentage(Auth::user()->id);
             
            $assessment_lesson = CourseLessonBasicInfo::where('course_basic_info_id',$assessment->courseBasicInfo->id)->where('status',1)->get();
           
            $unitArray=[];
            foreach($assessment_lesson as $assessment_lesson_info) {
                //print_r($assessment_lesson_info);
            $assessment_information = ActiveRecallDetails::where('lesson_id',$assessment_lesson_info->id)->where('status',1)->first();
            $assessment_lesson_info['assessment_information'] = $assessment_information;
  
            $assessment_complete=0;
            $assessment_lesson_info['assessment_complete'] = $assessment_complete;
      
            $assessment_setting = [];
            $assessment_lesson_info['assessment_setting'] = $assessment_setting;
            if($assessment_information){
                $assessment_lesson_count = ActiveRecallQuestion::where('quiz_id',$assessment_information->id)->where('status',1)->count();
                $assessment_lesson_info['lesson_count'] = $assessment_lesson_count;
            } else {
                $assessment_lesson_info['lesson_count'] = 0;
                        }            
            $unitArray[]=$assessment_lesson_info;
            }
            $assessment['lesson'] = $unitArray;
            $assessment['coursePercentage'] = $assessment_course_percentage;
            //print_r($assessment_lesson);
            $assessmentsArray[]=$assessment;
            }
        }

        //print_r($assessmentsArray);
        $assessments = $assessmentsArray;
 
        //print_r($courseArray);
        //print_r($assessments); 
        //exit;

        return view('activerecall.my_activerecall', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('assessments'));
    }
    
    public function activerecallWrite($id)
    {
        //$lid = $_REQUEST['lid'];
        
        $assessmentDetails = ActiveRecallDetails::with(['courseBasicInfo','activerecallQuestions'])->where('id',$id)->first();            
        if(count($assessmentDetails->activerecallQuestions) == 0){
            return redirect()->route('my_activerecall')->with('error','No questions found');
        }
        return view('activerecall.write', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('assessmentDetails'));
    }

    
    public function assessmentRewrite($id,$cid,$ccid,$lid)
    {
        //$lid = $_REQUEST['lid'];
         
        $assessmentAnswer = AssessmentAnswer::where('quiz_id',$id)->where('user_id',Auth::user()->id)->count();
        if($assessmentAnswer>0){            
            
            $assessmentResubmit = AssessmentResubmit::where('course_id',$cid)->where('course_category_id',$ccid)->where('lesson_id',$lid)->where('user_id',Auth::user()->id)->where('quiz_id',$id)->count();
       
            if($assessmentResubmit>0){  

            } else {

            $rewrite = new AssessmentResubmit([
                'course_id' => $cid,
                'course_category_id' =>  $ccid,
                'lesson_id' => $lid,
                'user_id' => auth()->user()->id,
                'quiz_id' => $id,
                'created_by' => auth()->user()->id,
                'status' => 1,
            ]);
            // Save the UserGroup instance to the database
            $rewrite->save();
            }
        } 
        return redirect()->route('my_assessment')->with('success','Resubmit Request Send Successfully'); 
    }

    public function manageactiverecall($id)
    {
        $quizID = $id;
        $quizInfo = ActiveRecallDetails::with(['courseBasicInfo','courseLessonBasicInfo'])->where('id',$id)->first();
        $QuizQuestions = ActiveRecallQuestion::where('quiz_id',$id)->get();   
        $quizSettingInfo = '';
        /*
        $quizSettingInfo = AssessmentSetting::where('course_id',$quizInfo->course_id)
                            ->where('course_category_id',$quizInfo->course_category_id)
                            ->where('lesson_id',$quizInfo->lesson_id)
                            ->where('status',1)
                            ->first();
        */                    

                            
        $getChapeterdetails=Content::where('course_id',$quizInfo->course_id)
        ->where('course_category_id',$quizInfo->course_category_id)
        ->where('course_lesson_id',$quizInfo->lesson_id)
        ->get();
 

        if($quizSettingInfo){
            $quizSettingID = $quizSettingInfo['id'];
        } else {
            $quizSettingID = 0;
        }                     
        $quizSettingID = 1;

        return view('activerecall.activerecallquestionlist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','QuizQuestions','quizID','quizSettingID','quizSettingInfo','getChapeterdetails'));
    }

    
    public function editactiverecallquestion($id)
    {
        $quizQuestion = ActiveRecallQuestion::find($id);
        $quizID = $quizQuestion->quiz_id;
        $quizInfo = ActiveRecallDetails::with(['courseBasicInfo','courseLessonBasicInfo'])->where('id',$quizID)->first();
        $quizSettingInfo = '';
        /*
        $quizSettingInfo = AssessmentSetting::where('course_id',$quizInfo->course_id)
                            ->where('course_category_id',$quizInfo->course_category_id)
                            ->where('lesson_id',$quizInfo->lesson_id)
                            ->where('status',1)
                            ->first();
        */

        $getChapeterdetails=Content::where('course_id',$quizInfo->course_id)
        ->where('course_category_id',$quizInfo->course_category_id)
        ->where('course_lesson_id',$quizInfo->lesson_id)
        ->get();

        
        if($quizSettingInfo){
            $quizSettingID = $quizSettingInfo['id'];
        } else {
            $quizSettingID = 0;
        }                     
        $quizSettingID = 1;

        return view('activerecall.editactiverecallquestion', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','quizQuestion','quizID','quizSettingID','quizSettingInfo','getChapeterdetails'));
    }

    public function deleteactiverecallquestion($id)
    {
        try {
            $quizQuestion = ActiveRecallQuestion::find($id);
             
            $quizID = $quizQuestion->quiz_id;
            if ($quizQuestion) {
                $quizQuestion->delete();
            }
        } catch (\Exception $e) {
        
            return redirect()->route('manageactiverecall',$quizID)->with('error', 'Active Recall Question not able to Delete, Try Again');
    
        }
        
        return redirect()->route('manageactiverecall',$quizID)->with('success', 'Active Recall Question Deleted Successfully'); 
    }

    public function addactiverecallquestion(Request $request)
    {
         $quizID = $request->input('quiz_id');
             
         
        // Create a new UserGroup instance
        $batch = new ActiveRecallQuestion([
            'quiz_id' => $request->input('quiz_id'),
            'question_name' => $request->input('question_name'),
            'answer' =>  $request->input('answer'),
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        // Save the UserGroup instance to the database
        $batch->save();
        return redirect()->route('manageactiverecall',$quizID)->with('success', 'Create Question Successfully');
    }

    
    public function updateactiverecallquestion(Request $request)
    {
        $id = $request->input('id');
        $quizID = $request->input('quiz_id');

        $batch = ActiveRecallQuestion::find($request->id);
        if ($batch) {
            $batch->update([                
                'question_name' => $request->input('question_name'),
                'answer' =>  $request->input('answer'),
            ]);
        }
         
        return redirect()->route('manageactiverecall',$quizID)->with('success', 'Modify Question Successfully');
    }

    public function ChangeStatusActiveRecall(Request $request)
    {
        $quizDetails = ActiveRecallDetails::where('id', $request->quiz_id)->first();
        $quizDetails->status = $request->status;
        $quizDetails->save();
        return response()->json(['success' => 'Status change successfully.']);
    }
    
    public function ChangeStatusAssessmentSetting(Request $request)
    {
        $quizDetails = AssessmentSetting::where('id', $request->quiz_id)->first();
        $quizDetails->status = $request->status;
        $quizDetails->save();
        return response()->json(['success' => 'Status change successfully.']);
    }

    public function saveassessmentWrite(Request $request){

        /*
        AssessmentID=4&CourseID=13&UserID=4&TotalQuestion=2&
        TotalMark=2&
        questionAnswers%5B%5D=41&
        questionAnswers%5B%5D=47&questionIDs%5B%5D=One&questionIDs%5B%5D=Two
        */

        $assessmentDetail = AssessmentDetails::where('id', $request->AssessmentID)->first();
        
        $assessmentQuestions = AssessmentQuestion::where('quiz_id', $request->AssessmentID)->get();

        $AssessmentID = $request->AssessmentID;
        $CourseID = $request->CourseID;
        $UserID = $request->UserID;
        $TotalQuestion = $request->TotalQuestion;
        $TotalMark = $request->TotalMark;        
        $questionIDs = $request->questionIDs;
        $questionAnswers = $request->questionAnswers;

        if(isset($questionIDs)){
            foreach ($questionIDs as $index => $questionID){
                
                AssessmentAnswer::create([
                    'user_id' => $UserID,
                    'quiz_id' => $AssessmentID,
                    'question_id' => $questionIDs[$index],
                    'question_name' => $questionIDs[$index],
                    'answer' => $questionAnswers[$index],
                    'status' => 1,
                    'created_by' => Auth::user()->id,
                ]); 

            }
        }

        if($request){
            return response()->json(['success' => 'Successfully Submitted', 'Data' => $request->questionIDs]);

        }else{
            return response()->json(['error' => 'issues on submit']);
        }
        print_r($request); exit;
    }

}
