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

use App\Models\CertificateDetails;
use App\Models\LearnerCertificate;

class AssessmentController extends Controller
{
    public function addassessment()
    {
        $courses = CourseBasicInfo::all();      
        $categories = CourseCategory::all();
        return view('assessment.addassessment', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('courses','categories'));
    }

    
    public function store(Request $request)
    {
        $customMessages = [
            'pdfresourese.required' => 'The PDF file is required.',
            'pdfresourese.mimes' => 'The file must be a PDF.',
            'pdfresourese.max' => 'The PDF may not be greater than 20 MB.',
        ];

        $request->validate([
            'pdfresourese' => 'required|mimes:pdf|max:20480', 
        ], $customMessages);

        if ($request->file('pdfresourese')->isValid()) {

        } else {
            return redirect()->back()->with('error', 'Assessment Agrement should PDF only.');
          
            //return redirect()->route('addassessment')->with('error', 'Assessment Agrement should PDF only.');
            //return back()->withErrors(['pdf' => 'There was an error uploading the file.']);

        }

        $request->content_id=0;
        $QuizquestionID = AssessmentDetails::where('course_category_id',$request->course_category_id)
        ->where('lesson_id',$request->lesson_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id;

            if ($request->hasFile('pdfresourese')) 
            {
               
                $file = $request->file('pdfresourese');
                $originalName = $file->getClientOriginalName();
                // $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assessment'), $originalName); 

                $QuizDetails = AssessmentDetails::findOrFail($quizID);
                $QuizDetails->url = $originalName;
                $QuizDetails->status = 1;
                $QuizDetails->created_by = Auth::user()->id;
                $QuizDetails->save();

                 
            }


        }
        else
        {     

        $count =AssessmentDetails::count(); 

        if ($request->hasFile('pdfresourese')) 
        {
            $file = $request->file('pdfresourese');
            $originalName = $file->getClientOriginalName();
            // $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assessment'), $originalName);
        }
        
        $QuizDetails = new AssessmentDetails();
        $QuizDetails->course_id = $request->course_id;
        $QuizDetails->lesson_id = $request->lesson_id;
        $QuizDetails->course_category_id = $request->course_category_id;
        $QuizDetails->url = $originalName;
        $QuizDetails->status = 1;
        $QuizDetails->created_by = Auth::user()->id;
        $QuizDetails->save();

        $quizID = $QuizDetails->id;
        }
 
         
        
        return redirect()->route('manageassessment',$quizID)
        ->with('success', 'Assessment created successfully.');
    }

    public function storeBK(Request $request)
    {
        $QuizquestionID = AssessmentDetails::where('course_category_id',$request->course_category_id)
        ->where('content_id',$request->content_id)
        ->where('lesson_id',$request->lesson_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id;
        }
        else
        {     

        $count =AssessmentDetails::count();
        $question_id = "Assessment".$count;
        
        $QuizDetails = new AssessmentDetails();
        $QuizDetails->course_id = $request->course_id;
        $QuizDetails->lesson_id = $request->lesson_id;
        $QuizDetails->content_id = $request->content_id;
        $QuizDetails->course_category_id = $request->course_category_id;
        $QuizDetails->question_id = $question_id;
        $QuizDetails->status = 1;
        $QuizDetails->created_by = Auth::user()->id;
        $QuizDetails->save();

        $quizID = $QuizDetails->id;
        }

        $questionsData = $request->all();
        if(isset($questionsData['question_name'])){
            foreach ($questionsData['question_name'] as $index => $questionName){
                AssessmentQuestion::create([
                    'quiz_id' => $quizID,
                    //'question_id' => $question_id,
                    'question_name' => $questionName,
                    'option1' => $questionsData['option1'][$index],
                    'option2' => $questionsData['option2'][$index],
                    'option3' => $questionsData['option3'][$index],
                    'option4' => $questionsData['option4'][$index],
                    'answer' => $questionsData['answer'][$index],
                    'answer_explanation' => $questionsData['Answerexplantion'][$index],
                    //question type is 1 for assessment multiple choice question
                    'question_type'=> 1,
                    'status' => 1,
                    'created_by' => Auth::user()->id,
                ]);
            }
        }
        
        if(isset($questionsData['shortquestion_name'])){
            foreach ($questionsData['shortquestion_name'] as $index => $questionName){
                AssessmentQuestion::create([
                    'quiz_id' => $quizID,
                    //'question_id' => $question_id,
                    'question_name' => $questionName,
                    //question type is 2 for assessment short answer question
                    'question_type'=> $questionsData['answer_length'][$index],
                    'section'=> $questionsData['section'][$index],
                    'type'=> $questionsData['type'][$index],
                    'status' => 1,
                    'created_by' => Auth::user()->id,
                ]);
            }
        }
        
        return redirect()->route('addassessment')
        ->with('success', 'Assessment created successfully.');
    }

    public function assessmentlist()
    {
        $assessments = AssessmentDetails::with(['courseBasicInfo','courseLessonBasicInfo'])->get();
        //$assessments = AssessmentDetails::join('course_basic_infos', 'assessment_details.course_id', '=', 'course_basic_infos.id')->get();
        return view('assessment.assessmentlist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('assessments'));
    }

    public function assessmentsettinglist()
    {
          
        $categories = CourseCategory::all();
        $assessments = AssessmentSetting::with(['courseBasicInfo','courseLessonBasicInfo'])->get();
        //$assessments = AssessmentDetails::join('course_basic_infos', 'assessment_details.course_id', '=', 'course_basic_infos.id')->get();
        return view('assessment.assessmentsettinglist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('assessments','categories'));
    }

    public function addassessmentsetting(Request $request)
    {
        $QuizquestionID = AssessmentSetting::where('course_category_id',$request->course_category_id)
        ->where('lesson_id',$request->lession_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id;
            
            return redirect()->route('assessmentsettinglist')
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
        
        return redirect()->route('assessmentsettinglist')
        ->with('success', 'Assessment Setting created successfully.');
    }

    public function editassessmentsetting($id)
    {
        $quizQuestion = AssessmentSetting::find($id);
        $quizID = $quizQuestion->quiz_id;
        $quizInfo = AssessmentSetting::with(['courseBasicInfo','courseLessonBasicInfo'])->where('id',$id)->first();
        //$quizQuestion = '';
                    
        return view('assessment.editassessmentsetting', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','quizQuestion','quizID'));
    }

    public function deleteassessmentsetting($id)
    {
        try {
            $quizQuestion = AssessmentSetting::find($id);
             
            $quizID = $quizQuestion->quiz_id;
            if ($quizQuestion) {
                $quizQuestion->delete();
            }
        } catch (\Exception $e) {
        
            return redirect()->route('assessmentsettinglist',$quizID)->with('error', 'Assessment Setting not able to Delete, Try Again');
    
        }
        
        return redirect()->route('assessmentsettinglist',$quizID)->with('success', 'Assessment Setting Deleted Successfully'); 
    }

    public function updateassessmentsetting(Request $request)
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
         
        return redirect()->route('assessmentsettinglist',$quizID)->with('success', 'Updated Assessment Setting Successfully');
    }


    public function myAssessment()
    {
        $my_course_ids = Entrollment::where('user_id',Auth::user()->id)->where('enrolled','1')->pluck('course_id')->toArray();
        //print_r($my_course_ids);
        $assessments = AssessmentDetails::with(['courseBasicInfo','courseLessonBasicInfo','assessmentQuestions'])->whereIn('course_id',$my_course_ids)->where('status',true)->get();
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
            $assessment_information = AssessmentDetails::where('lesson_id',$assessment_lesson_info->id)->where('status',1)->first();
            $assessment_lesson_info['assessment_information'] = $assessment_information;

            //print_r($assessment_information);
            if($assessment_information){
            $assessment_complete=0;
            $assessmentAnswer = AssessmentAnswer::where('quiz_id',$assessment_information->id)->where('user_id',Auth::user()->id)->count();
            if($assessmentAnswer>0){            
                $assessment_complete=1;
            } else {
                $assessment_complete=0;
            }
            $assessment_lesson_info['assessment_complete'] = $assessment_complete;
            
            $assessment_review=0;
            $assessmentAnswerReview = AssessmentAnswer::where('quiz_id',$assessment_information->id)->where('review_status',1)->where('user_id',Auth::user()->id)->count();
            if($assessmentAnswerReview>0){            
                $assessment_review=1;
            } else {
                $assessmentAnswerReview = AssessmentAnswer::where('quiz_id',$assessment_information->id)->where('review_status',2)->where('user_id',Auth::user()->id)->count();
                if($assessmentAnswerReview>0){   
                $assessment_review=2;
                } else {
                 $assessment_review=0;
                }
            }
            $assessment_lesson_info['assessment_review'] = $assessment_review;

            } else {    
            $assessment_complete=0;
            $assessment_lesson_info['assessment_complete'] = $assessment_complete;
            $assessment_review=0;
            $assessment_lesson_info['assessment_review'] = $assessment_review;

            }

            $assessment_setting = AssessmentSetting::where('lesson_id',$assessment_lesson_info->id)->where('status',1)->first();
            $assessment_lesson_info['assessment_setting'] = $assessment_setting;
            if($assessment_information){
                $assessment_lesson_count = AssessmentQuestion::where('quiz_id',$assessment_information->id)->where('status',1)->count();
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

        return view('assessment.my_assessment', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('assessments'));
    }
    
    public function assessmentWrite($id)
    {
        $lid = $_REQUEST['lid'];
        /*$CheckCourseCompleted = CourseCompleted::where('lesson_id',$lid)->where('user_id',Auth::user()->id)->count();
        if($CheckCourseCompleted == 0){            
            return redirect()->route('my_assessment')->with('error','Please Complete Course, Before assessment');
        }*/
        $assessmentAnswer = AssessmentAnswer::where('quiz_id',$id)->count();
        if($assessmentAnswer>0){            
            return redirect()->route('my_assessment')->with('error','Already Completed this one');
        }
        $assessmentDetails = AssessmentDetails::with(['courseBasicInfo','assessmentQuestions'])->where('id',$id)->first();            
        if(count($assessmentDetails->assessmentQuestions) == 0){
            return redirect()->route('my_assessment')->with('error','No questions found');
        }
        return view('assessment.write', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('assessmentDetails'));
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

    public function manageassessment($id)
    {
        $quizID = $id;
        $quizInfo = AssessmentDetails::with(['courseBasicInfo','courseLessonBasicInfo'])->where('id',$id)->first();
        $QuizQuestions = AssessmentQuestion::where('quiz_id',$id)->get();   
        $quizSettingInfo = AssessmentSetting::where('course_id',$quizInfo->course_id)
                            ->where('course_category_id',$quizInfo->course_category_id)
                            ->where('lesson_id',$quizInfo->lesson_id)
                            ->where('status',1)
                            ->first();

                            
        $getChapeterdetails=Content::where('course_id',$quizInfo->course_id)
        ->where('course_category_id',$quizInfo->course_category_id)
        ->where('course_lesson_id',$quizInfo->lesson_id)
        ->get();
 

        if($quizSettingInfo){
            $quizSettingID = $quizSettingInfo['id'];
        } else {
            $quizSettingID = 0;
        }                     

        return view('assessment.assessmentquestionlist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','QuizQuestions','quizID','quizSettingID','quizSettingInfo','getChapeterdetails'));
    }

    
    public function editassessmentquestion($id)
    {
        $quizQuestion = AssessmentQuestion::find($id);
        $quizID = $quizQuestion->quiz_id;
        $quizInfo = AssessmentDetails::with(['courseBasicInfo','courseLessonBasicInfo'])->where('id',$quizID)->first();
        $quizSettingInfo = AssessmentSetting::where('course_id',$quizInfo->course_id)
                            ->where('course_category_id',$quizInfo->course_category_id)
                            ->where('lesson_id',$quizInfo->lesson_id)
                            ->where('status',1)
                            ->first();
        $getChapeterdetails=Content::where('course_id',$quizInfo->course_id)
        ->where('course_category_id',$quizInfo->course_category_id)
        ->where('course_lesson_id',$quizInfo->lesson_id)
        ->get();

        
        if($quizSettingInfo){
            $quizSettingID = $quizSettingInfo['id'];
        } else {
            $quizSettingID = 0;
        }                     

        return view('assessment.editassessmentquestion', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','quizQuestion','quizID','quizSettingID','quizSettingInfo','getChapeterdetails'));
    }

    public function deleteassessmentquestion($id)
    {
        try {
            $quizQuestion = AssessmentQuestion::find($id);
             
            $quizID = $quizQuestion->quiz_id;
            if ($quizQuestion) {
                $quizQuestion->delete();
            }
        } catch (\Exception $e) {
        
            return redirect()->route('manageassessment',$quizID)->with('error', 'Assessment Question not able to Delete, Try Again');
    
        }
        
        return redirect()->route('manageassessment',$quizID)->with('success', 'Assessment Question Deleted Successfully'); 
    }

    public function addassessmentquestion(Request $request)
    {
         $quizID = $request->input('quiz_id');
         $quizSettingID = $request->input('quiz_setting_id');
         $quizSettingSection1 = $request->input('quiz_setting_section1');
         $quizSettingSection2 = $request->input('quiz_setting_section2');

         $quizSettingCheckSection1 = AssessmentQuestion::where('quiz_id',$request->input('quiz_id'))
                            ->where('section',1)
                            ->count();
                            
         $quizSettingCheckSection2 = AssessmentQuestion::where('quiz_id',$request->input('quiz_id'))
         ->where('section',2)
         ->count();

         if($request->input('section')==1){

            if($quizSettingSection1 > $quizSettingCheckSection1){

            } else {
                return redirect()->route('manageassessment',$quizID)->with('error', 'Section 1 Question Limit exceed.');
            }

         } else if($request->input('section')==2){

            if($quizSettingSection2 > $quizSettingCheckSection2){

            } else {
                return redirect()->route('manageassessment',$quizID)->with('error', 'Section 2 Question Limit exceed.');
            }

         }                 

        /*
         echo "<hr/>";
         echo $quizSettingSection1;
         echo "<hr/>";
         echo $quizSettingSection2;
         echo "<hr/>";
         echo $quizSettingCheckSection1;
         echo "<hr/>";
         echo $quizSettingCheckSection2;
         echo "<hr/>";
         exit;
         */

         
        // Create a new UserGroup instance
        $batch = new AssessmentQuestion([
            'quiz_id' => $request->input('quiz_id'),
            'question_name' => $request->input('question_name'),
            'section' =>  $request->input('section'),
            'type' => $request->input('type'),
            'chapter' => $request->input('chapter'),
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        // Save the UserGroup instance to the database
        $batch->save();
        return redirect()->route('manageassessment',$quizID)->with('success', 'Create Question Successfully');
    }

    
    public function updateassessmentquestion(Request $request)
    {
        $id = $request->input('id');
        $quizID = $request->input('quiz_id');

        $batch = AssessmentQuestion::find($request->id);
        if ($batch) {
            $batch->update([                
                'chapter' => $request->input('chapter'),
                'question_name' => $request->input('question_name'),
                'section' =>  $request->input('section'),
                'type' => $request->input('type'),
            ]);
        }
         
        return redirect()->route('manageassessment',$quizID)->with('success', 'Modify Question Successfully');
    }

    public function ChangeStatusAssessment(Request $request)
    {
        $quizDetails = AssessmentDetails::where('id', $request->quiz_id)->first();
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
