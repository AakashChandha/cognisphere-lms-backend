<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseBasicInfo;
use App\Models\CourseLessonBasicInfo;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Auth;
use App\Models\QuizDetails;
use App\Models\Entrollment;
use App\Models\Content;
use App\Models\CourseCategory;

class QuizController extends Controller
{
    public function addquizBK()
    {
        $courses = CourseBasicInfo::all();        
        $categories = CourseCategory::all();
        return view('quiz.add', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses','categories'));
    }
    public function addquiz()
    {
        $courses = CourseBasicInfo::all();        
        $categories = CourseCategory::all();
        return view('quiz.add', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses','categories'));
    }
    public function courselession(Request $request)
    {
        $course_id = $request->input('course_id');
        if(isset($course_id)){
            $lessons = CourseLessonBasicInfo::where('course_basic_info_id',$course_id)->get();
        }else{
            $lessons = CourseLessonBasicInfo::all();
        }
        return $lessons;
    }

    public function courselessionchapter(Request $request)
    {
        $lesson_id = $request->input('lesson_id');
        if(isset($lesson_id)){
            $lessons = Content::where('course_lesson_id',$lesson_id)->get();
        }else{
            $lessons = Content::all();
        }
        return $lessons;
    }

    
    public function submitBK(Request $request)
    {

        $QuizquestionID = QuizDetails::where('course_category_id',$request->course_category_id)
        ->where('content_id',$request->content_id)
        ->where('lesson_id',$request->lession_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id;
        }
        else
        {            
            
            $count =QuizDetails::count();
            $question_id = "quiz".$count;
            
            $QuizDetails = new QuizDetails();
            $QuizDetails->course_id = $request->course_id;
            $QuizDetails->course_category_id = $request->course_category_id;
            $QuizDetails->lesson_id = $request->lession_id;
            $QuizDetails->content_id = $request->content_id;
            $QuizDetails->question_id = $question_id;
            $QuizDetails->status = 1;
            $QuizDetails->created_by = Auth::user()->id;
            $QuizDetails->save();

            $quizID = $QuizDetails->id;
        }
        

        $questionsData = $request->all();
        foreach ($questionsData['question_name'] as $index => $questionName){
            QuizQuestion::create([
                'quiz_id' => $quizID,
                //'question_id' => $question_id,
                'question_name' => $questionName,
                'option1' => $questionsData['option1'][$index],
                'option2' => $questionsData['option2'][$index],
                'option3' => $questionsData['option3'][$index],
                'option4' => $questionsData['option4'][$index],
                'answer' => $questionsData['answer'][$index],
                'answer_explanation' => $questionsData['Answerexplantion'][$index],
                'status' => 1,
                'created_by' => Auth::user()->id,
            ]);
        }
        return redirect()->route('addquiz')
            ->with('success', 'quiz created successfully.');
    }

    public function submit(Request $request)
    {

        $QuizquestionID = QuizDetails::where('course_category_id',$request->course_category_id)
        ->where('content_id',$request->content_id)
        ->where('lesson_id',$request->lession_id)
        ->where('course_id',$request->course_id)
        ->first();
       
        if($QuizquestionID)
        {
            $quizID = $QuizquestionID->id;
        }
        else
        {            
            
            $count =QuizDetails::count();
            $question_id = "quiz".$count;
            
            $QuizDetails = new QuizDetails();
            $QuizDetails->course_id = $request->course_id;
            $QuizDetails->course_category_id = $request->course_category_id;
            $QuizDetails->lesson_id = $request->lession_id;
            $QuizDetails->content_id = $request->content_id;
            $QuizDetails->question_id = $question_id;
            $QuizDetails->status = 1;
            $QuizDetails->created_by = Auth::user()->id;
            $QuizDetails->save();

            $quizID = $QuizDetails->id;
        }
        
        /*
        $questionsData = $request->all();
        foreach ($questionsData['question_name'] as $index => $questionName){
            QuizQuestion::create([
                'quiz_id' => $quizID,
                //'question_id' => $question_id,
                'question_name' => $questionName,
                'option1' => $questionsData['option1'][$index],
                'option2' => $questionsData['option2'][$index],
                'option3' => $questionsData['option3'][$index],
                'option4' => $questionsData['option4'][$index],
                'answer' => $questionsData['answer'][$index],
                'answer_explanation' => $questionsData['Answerexplantion'][$index],
                'status' => 1,
                'created_by' => Auth::user()->id,
            ]);
        }
        */
        return redirect()->route('managequiz',$quizID)
            ->with('success', 'quiz created successfully.');
    }

    
    public function managequiz($id)
    {
        $quizID = $id;
        $quizInfo = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','content'])->where('id',$id)->first();
        $QuizQuestions = QuizQuestion::where('quiz_id',$id)->get();            
        return view('quiz.questionlist', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','QuizQuestions','quizID'));
    }

    public function quizlist()
    {
        $category_id = '';
        $course_id = '';
        $categories = CourseCategory::all();
        if(request('course_category_id'))
        {
            $courseCategoryId = request('course_category_id');     
            $category_id = $courseCategoryId; 
            $courses = CourseBasicInfo::where('course_category_id',$courseCategoryId)->get();  
            
            if(request('course_id'))
            {
            $courseId = request('course_id');
            $course_id = $courseId;
            $quizlists = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','content'])->where('course_category_id',$courseCategoryId)->where('course_id',$courseId)->get();
            } else {                 
            $quizlists = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','content'])->where('course_category_id',$courseCategoryId)->get();
            }
        } else {
        $courses = [];        
        $quizlists = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','content'])->get();
        }
        /*$quizlists = QuizDetails::join('course_basic_infos', 'quiz_details.course_id', '=', 'course_basic_infos.id')
            ->join('course_lesson_basic_infos', 'quiz_details.lesson_id', '=', 'course_lesson_basic_infos.id')
            ->select('quiz_details.*', 'course_basic_infos.course_name as course_name', 'course_lesson_basic_infos.lesson_name as lesson_name')
            ->get();*/
        return view('quiz.list', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizlists','courses','categories','category_id','course_id'));
    }
    public function my_quiz()
    {
        $my_course_ids = Entrollment::where('user_id',Auth::user()->id)->pluck('course_id')->toArray();
        $quiz_list = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','quizQuestions'])
        ->whereHas('courseBasicInfo', function ($query) use($my_course_ids){
            $query->whereIn('id', $my_course_ids);
        })
        ->where('status',true)->get();
        return view('quiz.myquiz', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quiz_list'));
    }
    public function quizwrite($id)
    {
        $quizDetails = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','quizQuestions'])->where('id',$id)->first();            
        if(count($quizDetails->quizQuestions) == 0){
            return redirect()->route('myquiz')->with('error','No questions found');
        }
        return view('quiz.write', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizDetails'));
    }

    public function quizwriteapi($id)
    {
        $quizDetails = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','quizQuestions'])->where('id',$id)->first();            
        if(count($quizDetails->quizQuestions) == 0){
           // return redirect()->route('myquiz')->with('error','No questions found');
        }
        //return response()->json(['success' => true, 'message' => $quizDetails]);
        return response()->json(['status' => 'success', 'data' => $quizDetails]);
        //return response()->json(['success' => 'Success', 'data' => $quizDetails]);
        //return view('quiz.write', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizDetails'));
    }

    public function quizcheckAnswer(Request $request)
    {
        //$quizDetails = QuizDetails::where('id', $request->quiz_id)->first();
        $quizQuestions = QuizQuestion::where('id', $request->question_id)->first();

        $correctAnswer = $quizQuestions->answer;

        $AnswerLeaner = $request->answer;

        $quizQuestions = QuizQuestion::where('id', $request->question_id)->first();

        $CheckOption = $quizQuestions->answer;


        $CorrectanswerCheck = QuizQuestion::where('id', $request->question_id)->first();
        $correctAnswerCheckArray = [
            'question_id' => $CorrectanswerCheck->question_id,
            'answer' => $CorrectanswerCheck->answer,
            'option1' => $CorrectanswerCheck->option1,
            'option2' => $CorrectanswerCheck->option2,
            'option3' => $CorrectanswerCheck->option3,
            'option4' => $CorrectanswerCheck->option4,
            'answer_explanation' => $CorrectanswerCheck->answer_explanation,
        ];

        $checkOptionValue = $correctAnswerCheckArray[$CheckOption];
        

        if($checkOptionValue == $AnswerLeaner){
            return response()->json(['success' => 'Correct Answer', 'correctAnswer' => $AnswerLeaner, 'answerexplanation' => $CorrectanswerCheck->answer_explanation]);

        }else{
            return response()->json(['error' => 'Wrong Answer', 'correctAnswer' => $checkOptionValue,'answerexplanation' => $CorrectanswerCheck->answer_explanation]);
        }
    }

    public function ChangeStatusQuiz(Request $request)
    {
        $quizDetails = QuizDetails::where('id', $request->quiz_id)->first();
        $quizDetails->status = $request->status;
        $quizDetails->save();
        return response()->json(['success' => 'Status change successfully.']);
    }

    public function  getChapterName($courseId,$catId,$unitId)
    {
        $getChapeterdetails=Content::where('course_id',$courseId)->where('course_category_id',$catId)->where('course_lesson_id',$unitId)->get();
        return response()->json($getChapeterdetails); 
    }

    public function editquestion($id)
    {
        $quizQuestion = QuizQuestion::find($id);
        $quizID = $quizQuestion->quiz_id;
        $quizInfo = QuizDetails::with(['courseBasicInfo','courseLessonBasicInfo','content'])->where('id',$quizID)->first();
                    
        return view('quiz.editquestion', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizInfo','quizQuestion','quizID'));
    }

    public function deletequestion($id)
    {
        try {
            $quizQuestion = QuizQuestion::find($id);
             
            $quizID = $quizQuestion->quiz_id;
            if ($quizQuestion) {
                $quizQuestion->delete();
            }
        } catch (\Exception $e) {
        
            return redirect()->route('managequiz',$quizID)->with('error', 'Quiz Question not able to Delete, Try Again');
    
        }
        
        return redirect()->route('managequiz',$quizID)->with('success', 'Quiz Question Deleted Successfully'); 
    }

    public function addquestion(Request $request)
    {
         $quizID = $request->input('quiz_id');
        // Create a new UserGroup instance
        $batch = new QuizQuestion([
            'quiz_id' => $request->input('quiz_id'),
            'question_name' => $request->input('question_name'),
            'option1' => $request->input('option1'),
            'option2' => $request->input('option2'),
            'option3' => $request->input('option3'),
            'option4' => $request->input('option4'),
            'answer' =>  $request->input('answer'),
            'answer_explanation' => $request->input('answer_explanation'),
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        // Save the UserGroup instance to the database
        $batch->save();
        return redirect()->route('managequiz',$quizID)->with('success', 'Create Question Successfully');
    }

    
    public function updatequestion(Request $request)
    {
        $id = $request->input('id');
        $quizID = $request->input('quiz_id');

        $batch = QuizQuestion::find($request->id);
        if ($batch) {
            $batch->update([
                'question_name' => $request->input('question_name'),
                'option1' => $request->input('option1'),
                'option2' => $request->input('option2'),
                'option3' => $request->input('option3'),
                'option4' => $request->input('option4'),
                'answer' =>  $request->input('answer'),
                'answer_explanation' => $request->input('answer_explanation'),
            ]);
        }
         
        return redirect()->route('managequiz',$quizID)->with('success', 'Modify Question Successfully');
    }
}
