<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Entrollment;
use App\Models\LearnerCertificate;
use App\Models\CertificateDetails;
use App\Models\CourseBasicInfo;
use App\Models\UserGroup;
use App\Models\User;
use App\Models\Batch;
use App\Models\MapCourse;
use App\Models\Session;
use App\Models\CourseCategory;
use App\Models\CourseCompleted;
use App\Models\Content;
use App\Models\QuizDetails;
use App\Models\QuizQuestion;
use App\Models\CourseCompletedTracking;
use App\Models\VideoContent;
use App\Models\ResoureseModel;
use App\Models\CourseLessonBasicInfo; 

use App\Models\AssessmentAnswer;
use App\Models\AssessmentResubmit;
use App\Models\AssessmentDetails;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSetting;

use App\Models\UserAccountBasicInfo;
use DB;
 
class InstructorController extends Controller
{
    //

    public function index()
    {
        return view('instructor.index', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb']);
    }

    public function learnerInstructor()
    {
        $authusersRole = Auth::user()->user_group_id;

        $userIdAuth = Auth::user()->email;
        if($authusersRole == 3)
        {
            $usergroups = UserGroup::all();
            $users = User::all();
            $usergroups = UserGroup::all();
            $users = User::all();

            $UserIdAuthGet = User::where('users.email', '=', $userIdAuth)->first();

            $mapcoursesid = MapCourse::where('instructor_id', '=', $UserIdAuthGet->id)->get();

            $sessionIDs=[];
            foreach($mapcoursesid as $mapcourseid)
            {
               $sessionIDs[] = $mapcourseid->session_id;
            }

            $courses = CourseBasicInfo::all();
            $usergroups = UserGroup::all();
            $usersAccounts = User::with(['userAccountBasicInfo'])->get();
            
            $entrollments = Entrollment::with(['userGroup','user','user.userAccountBasicInfo','courseBasicInfo','batch'])->where('enrolled', '=', 1)->whereIn('session_id', $sessionIDs)->get();
            /*$Entrollments = Entrollment::select(
                    'entrollment.id as enrollment_id',
                    'course_basic_infos.course_name',
                    'entrollment.batch_id',
                    'batch.batch_name',
                    'entrollment.course_type',
                    'user_groups.name as usergroup_name',
                    'useraccount.firstName as useraccount_first_name',
                    'useraccount.lastName as useraccount_last_name',
                    'entrollment.created_by',
                    'entrollment.status',
                    'entrollment.created_at',
                    'entrollment.updated_at'
                )
                ->join('user_groups', 'entrollment.user_group_id', '=', 'user_groups.id')
                ->join('useraccount', 'entrollment.user_id', '=', 'useraccount.id')
                ->join('course_basic_infos', 'entrollment.course_id', '=', 'course_basic_infos.id')
                ->join('batch', 'entrollment.batch_id', '=', 'batch.id')
                ->where('entrollment.enrolled', '=', 0)
                ->get();*/
    
            $batchs = Batch::where('status', '!=', 0)->get();
            return view('instructor.entrollmentcompleted', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('batchs','courses','usergroups','usersAccounts','entrollments'));
        
            /*

            $Entrollments = [];
            //$sessions = Session::where('status', '!=', 0)->get();

            // $courses = CourseBasicInfo::all();

            $mapcoursesid = MapCourse::where('instructor_id', '=', $UserIdAuthGet->id)->get();

            foreach($mapcoursesid as $mapcourseid)
            {
                $courses[] = CourseBasicInfo::where('id', '=', $mapcourseid->course_id)->first();
                $batchs[] = Batch::where('id', '=', $mapcourseid->batch_id)->where('status', '!=', 0)->first();
                $sessions[] = Session::where('batch_id', $mapcourseid->batch_id)->where('status', '!=', 0)->first();
            }
            return view('instructor.attendaceInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions'));
        
            */
        }
        else
        {
            $courses = CourseBasicInfo::all();
            $usergroups = UserGroup::all();
            $users = User::all();

            $courses = CourseBasicInfo::all();
            $usergroups = UserGroup::all();
            $users = User::all();

            $Entrollments = [];
            $batchs = Batch::where('status', '!=', 0)->get();
            $sessions = Session::where('status', '!=', 0)->get();

            return view('instructor.attendaceInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions'));
        }
    }    
    public function attendaceInstructor()
    {
        $authusersRole = Auth::user()->user_group_id;

        $userIdAuth = Auth::user()->email;
        if($authusersRole == 3)
        {
            $usergroups = UserGroup::all();
            $users = User::all();
            $usergroups = UserGroup::all();
            $users = User::all();

            $UserIdAuthGet = User::where('users.email', '=', $userIdAuth)->first();


            $Entrollments = [];
            //$sessions = Session::where('status', '!=', 0)->get();

            // $courses = CourseBasicInfo::all();

            $mapcoursesid = MapCourse::where('instructor_id', '=', $UserIdAuthGet->id)->get();

            foreach($mapcoursesid as $mapcourseid)
            {
                $courses[] = CourseBasicInfo::where('id', '=', $mapcourseid->course_id)->first();
                $batchs[] = Batch::where('id', '=', $mapcourseid->batch_id)->where('status', '!=', 0)->first();
                $sessions[] = Session::where('batch_id', $mapcourseid->batch_id)->where('status', '!=', 0)->first();
            }
            return view('instructor.attendaceInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions'));
        }
        else
        {
            $courses = CourseBasicInfo::all();
            $usergroups = UserGroup::all();
            $users = User::all();

            $courses = CourseBasicInfo::all();
            $usergroups = UserGroup::all();
            $users = User::all();

            $Entrollments = [];
            $batchs = Batch::where('status', '!=', 0)->get();
            $sessions = Session::where('status', '!=', 0)->get();

            return view('instructor.attendaceInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions'));
        }
    }

    public function mapping()
    {
        $categories = CourseCategory::all();
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $batches = Batch::where('status', '!=', 0)->get();
        $sessions = Session::where('status', '!=', 0)->get();
        /*
        $instructorUserGroup = UserGroup::where('role','instructor')->first();
        $userAccounts = User::where('user_group_id',$instructorUserGroup->id)->get();
        */
        $instructorUserGroups = UserGroup::where('role','instructor')->get();
        $insUserGroup=[];
        foreach($instructorUserGroups as $instructorUserGroup) {
            $insUserGroup[]=$instructorUserGroup->id;
        }
        $userAccounts = User::whereIn('user_group_id',$insUserGroup)->get();
        
        // $userAccounts = User::select('users.*')
        //     ->join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
        //     ->where('user_groups.role', '=', 'instructor')
        //     ->get();
        
        $mapCourses = MapCourse::where('status',1)->with(['courseBasicInfo','batch','instructor'])->get();
        /*$Mapcourses = MapCourse::select(
            'course_batches.id as mappingcourse_id',
            'course_basic_infos.course_name',
            'batches.batch_name',
            'user_account_basic_infos.first_name as useraccount_first_name',
            'user_account_basic_infos.last_name as useraccount_last_name',
            'course_batches.created_by',
            'course_batches.status',
            'course_batches.created_at',
            'course_batches.updated_at'
        )
            ->join('course_basic_infos', 'course_batches.course_id', '=', 'course_basic_infos.id')
            ->join('batches', 'course_batches.batch_id', '=', 'batches.id')
            ->join('user_account_basic_infos', 'course_batches.instructor_id', '=', 'user_account_basic_infos.user_id')
            ->where('course_batches.status', '!=', 0)
            ->get();*/


        $AvailableInstructorIDs = DB::table('users as u')
        ->leftJoin('course_batches as b', 'u.id', '=', 'b.instructor_id')
        ->where('u.user_group_id', 3)
        ->whereNull('b.instructor_id')
        ->pluck('u.id')
        ->toArray();   

        $AvailableInstructors = User::whereIn('id', $AvailableInstructorIDs)->get();
        
        $AvailableCourseIDs = DB::table('course_basic_infos as c')
        ->leftJoin('course_batches as b', 'c.id', '=', 'b.course_id')
        ->whereNull('b.course_id')
        ->pluck('c.id')
        ->toArray();

        $AvailableCourses = CourseBasicInfo::whereIn('id', $AvailableCourseIDs)->get();
        
        return view('instructor.mapping', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('categories','courses','batches','sessions','usergroups', 'userAccounts', 'mapCourses','AvailableCourses','AvailableInstructors'));
    }

    public function addmapping(Request $request)
    {

        $course_category_id = $request->input('course_category_id');
        $course_id = $request->input('course_id');
        $instructor_id = $request->input('instructor_id');
        $batch_id = $request->input('batch_id');
        $session_id = $request->input('session_id');

        $UnitCount = MapCourse::where('course_category_id',$course_category_id)
                    ->where('course_id',$course_id)
                    ->where('instructor_id',$instructor_id)
                    ->where('batch_id',$batch_id)
                    ->where('session_id',$session_id)
                    ->where('status',1)
                    ->count();

        if($UnitCount>0){
            return redirect()->route('mapping')->with('error', 'Mapping Already Exist.');
        } else {

        $mapping = new MapCourse();
        $mapping->course_category_id = $request->course_category_id;
        $mapping->course_id = $request->course_id;
        $mapping->instructor_id = $request->instructor_id;
        $mapping->batch_id = $request->batch_id;
        $mapping->session_id = $request->session_id;
        $mapping->status = 1;
        $mapping->created_by = Auth::user()->id;
        $mapping->save();

        return redirect()->route('mapping')->with('success', 'Mapping Added Successfully');

        }
    }

    public function editmapping($id)
    {
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $batchs = Batch::where('status', '!=', 0)->get();        
        $sessions = Session::where('status', '!=', 0)->get();
        
        $userAccounts = User::select('users.*')
            ->join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
            ->where('user_groups.role', '=', 'instructor')
            ->get();
        
            $Mapcourses = MapCourse::select(
                'course_batches.id as mappingcourse_id',
                'course_basic_infos.course_name',
                'batches.batch_name',
                'user_account_basic_infos.first_name as useraccount_first_name',
                'user_account_basic_infos.last_name as useraccount_last_name',
                'course_batches.created_by',
                'course_batches.status',
                'course_batches.created_at',
                'course_batches.updated_at')
                ->join('course_basic_infos', 'course_batches.course_id', '=', 'course_basic_infos.id')
                ->join('batches', 'course_batches.batch_id', '=', 'batches.id')
                ->join('user_account_basic_infos', 'course_batches.instructor_id', '=', 'user_account_basic_infos.user_id')
                ->get();
        
        $editModel = true;
        $EditMapcourses = MapCourse::find($id);

        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $batchs = Batch::where('status', '!=', 0)->where('batch_course', $EditMapcourses->course_id)->get();        
        $sessions = Session::where('status', '!=', 0)->where('batch_id', $EditMapcourses->batch_id)->get();

        
        $AvailableInstructorIDs = DB::table('users as u')
        ->leftJoin('course_batches as b', 'u.id', '=', 'b.instructor_id')
        ->where('u.user_group_id', 3)
        ->whereNull('b.instructor_id')
        ->pluck('u.id')
        ->toArray();   

        $AvailableInstructors = User::whereIn('id', $AvailableInstructorIDs)->get();
        
        $AvailableCourseIDs = DB::table('course_basic_infos as c')
        ->leftJoin('course_batches as b', 'c.id', '=', 'b.course_id')
        ->whereNull('b.course_id')
        ->pluck('c.id')
        ->toArray();

        $AvailableCourses = CourseBasicInfo::whereIn('id', $AvailableCourseIDs)->get();
        
        return view('instructor.mapping', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses','EditMapcourses','editModel','batchs','sessions','usergroups', 'userAccounts', 'Mapcourses','AvailableCourses','AvailableInstructors'));
    }

    public function updatemapping(Request $request)
    {
        $course_category_id = $request->input('course_category_id');
        $course_id = $request->input('course_id');
        $instructor_id = $request->input('instructor_id');
        $batch_id = $request->input('batch_id');
        $session_id = $request->input('session_id');

        $UnitCount = MapCourse::where('course_category_id',$course_category_id)
                    ->where('course_id',$course_id)
                    ->where('instructor_id',$instructor_id)
                    ->where('batch_id',$batch_id)
                    ->where('session_id',$session_id)
                    ->where('status',1)
                    ->where('id','!=',$request->mappingcourse_id)
                    ->count();

        if($UnitCount>0){
            return redirect()->route('mapping')->with('error', 'Mapping Already Exist.');
        } else {


        $mapping = MapCourse::find($request->mappingcourse_id);
        $mapping->course_id = $request->course_id;
        $mapping->instructor_id = $request->instructor_id;
        $mapping->batch_id = $request->batch_id;
        $mapping->session_id = $request->session_id;
        $mapping->status = 1;
        $mapping->created_by = Auth::user()->id;
        $mapping->save();

        return redirect()->route('mapping')->with('success', 'Mapping Updated Successfully');

        }
    }

    public function deletemapping($id)
    {
        $mapping = MapCourse::find($id);
        $mapping->status = 0;
        $mapping->save();

        return redirect()->route('mapping')->with('success', 'Mapping Deleted Successfully');
    }

    public function instructors()
    {


        $learner_count = 0;
        $resubmit_count = 0;
        $review_count = 0;

        $entrollments = Entrollment::where('enrolled', '=', 1)->get();
        $learner_count = count($entrollments);

        
        $resubmit = AssessmentResubmit::where('status', '=', 1)->get();
        $resubmit_count = count($resubmit);

        $review = AssessmentAnswer::where('review_status',0)->select('user_id', 'quiz_id')->distinct()->get();
        $review_count = count($review);

        return view('instructor.index', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('learner_count','resubmit_count','review_count'));
    }


    public function viewCourseInfo(Request $request)
    {

        $useremail = Auth::user()->email;
        $UserAccountId = User::where('email',$useremail)->first();
        $mycoursesId = Entrollment::where('id',$request->id )->first();
         
        $id= $mycoursesId->course_id;
        $user_id= $mycoursesId->user_id;

        $course = CourseBasicInfo::find($id);

        $instustcorMaping =MapCourse::where('course_id',$id)->first();

        $instudtcorUser = $instustcorMaping->instructor_id;

        $instustcorMapinguser = User::find($instustcorMaping->instructor_id);

        $CheckCourseCompleted = CourseCompleted::where('course_id',$id)->where('user_id',$user_id)->count();

        $CourseCompletedDetails = CourseCompleted::where('course_id',$id)->where('user_id',$user_id)->first();

        $content_id = '';

        
            
        $coursecompletedtracking = CourseCompletedTracking::where('course_id',$id)->where('user_id',$user_id)->get();

        $courseTrackingLastiDs = CourseCompletedTracking::where('course_id',$id)->where('user_id',$user_id)->where('complete',0)->first();

        $Totalcount = CourseCompletedTracking::where('course_id',$id)->where('user_id',$user_id)->count();
        $CompletedCount = CourseCompletedTracking::where('course_id',$id)->where('user_id',$user_id)->where('progress',1)->count();

        $percentage = 0;
        if($Totalcount > 0){
            $percentage = ($CompletedCount / $Totalcount) * 100;
        }

        $lastseesioncontentid = 0;
        if($courseTrackingLastiDs == null)
        {
            $courseTrackingLastiDs = CourseCompletedTracking::where('course_id',$id)->where('user_id',$user_id)->where('complete',0)->first();
            //print_r($courseTrackingLastiDs);
            if(isset($courseTrackingLastiDs)){
                $lastseesioncontentid =  $courseTrackingLastiDs->content_id;
            }
        }
        else
        {
            $lastseesioncontentid =  $courseTrackingLastiDs->content_id;
        }

        $CourseId = $id;

        $videoContents = VideoContent::where('course_id', $id)->get();

        $resoureseContents = ResoureseModel::where('course_id',$id)->get();

        $courseLessonBasicInfos = CourseLessonBasicInfo::with(['content' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('course_basic_info_id', $id)
        ->where('course_category_id', $course->course_category_id)
        ->get();

       return view('instructor.view-course-info', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('CourseId','percentage','lastseesioncontentid','instustcorMapinguser','content_id','CourseCompletedDetails','coursecompletedtracking','videoContents','resoureseContents','course','courseLessonBasicInfos'));
    }

    
    public function assessmentRequestInstructor()
    {
        $authusersRole = Auth::user()->user_group_id;
        
        $userIdAuth = Auth::user()->email;
         
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();

        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();

        $Entrollments = [];
        $batchs = Batch::where('status', '!=', 0)->get();
        $sessions = Session::where('status', '!=', 0)->get();

        $AssessmentResubmit = AssessmentResubmit::with(['courseBasicInfo','courseLessonBasicInfo','userBasicInfo','assessmentQuestions'])->where('status', '=', 1)->get();
       

        //exit;

        return view('instructor.assessmentResubmitInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions','AssessmentResubmit'));
 
    }    

    
    public function assessmentRequestAccept($id)
    {
        //$AssessmentResubmit = AssessmentResubmit::find($id);
        $AssessmentResubmit = AssessmentResubmit::with(['courseBasicInfo','courseLessonBasicInfo','userBasicInfo','assessmentQuestions'])->where('id', '=', $id)->first();

        if ($AssessmentResubmit) {
            $user_id = $AssessmentResubmit->user_id;
            $quiz_id = $AssessmentResubmit->quiz_id;

            $AssessmentAnswer = AssessmentAnswer::where('user_id', $user_id)->where('quiz_id', $quiz_id)->delete();
            
        }


        if ($AssessmentResubmit) {
            $AssessmentResubmit->delete();
        }
        return redirect()->route('assessmentRequestInstructor')->with('success', 'Request Accepted Successfully');
   
    }

    public function assessmentRequestDecline($id)
    {
        //$AssessmentResubmit = AssessmentResubmit::find($id);
        $AssessmentResubmit = AssessmentResubmit::with(['courseBasicInfo','courseLessonBasicInfo','userBasicInfo','assessmentQuestions'])->where('id', '=', $id)->first();

        if ($AssessmentResubmit) {
            $AssessmentResubmit->delete();
        }
        return redirect()->route('assessmentRequestInstructor')->with('success', 'Request Deleted Successfully');
   
    }    


    public function assessmentReviewInstructor()
    {
        $authusersRole = Auth::user()->user_group_id;
        
        $userIdAuth = Auth::user()->email;
         
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();

        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();

        $Entrollments = [];
        $batchs = Batch::where('status', '!=', 0)->get();
        $sessions = Session::where('status', '!=', 0)->get();

        $AssessmentReviewQuiz = AssessmentAnswer::where('review_status',0)->select('user_id', 'quiz_id')->distinct()->get();
       
        $AssessmentReviewArray=[];
        $a=0;
        foreach($AssessmentReviewQuiz as $AssessmentReviewQuizDetail){

            
           $UserDetail = UserAccountBasicInfo::where('status', '=', 1)
           ->where('user_id',$AssessmentReviewQuizDetail->user_id)->first();
       
            $AssessmentReviewArray[$a]['userInfo']=$UserDetail;
            
           $AssessmentDetail = AssessmentDetails::with([
            'courseBasicInfo',
            'courseLessonBasicInfo'
            ])->where('status', '=', 1)->where('id',$AssessmentReviewQuizDetail->quiz_id)->first();
       

            $AssessmentReviewArray[$a]['assessmentInfo']=$AssessmentDetail;
            
            $a++;
        }



        //print_r($AssessmentReviewArray); exit;
 

        return view('instructor.assessmentReviewInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions','AssessmentReviewArray'));
 
    }    
    
     
    public function assessmentReview($id,$uid)
    {
        //$lid = $_REQUEST['lid'];
        
        $assessmentDetails = AssessmentDetails::with([
            'courseBasicInfo',
            'assessmentSetting',
            'assessmentQuestions',
            'assessmentAnswers' => function ($query) use ($id, $uid) {
                $query->where('quiz_id', $id)->where('user_id',$uid);
            }
            ])->where('id',$id)->first();            
      
        //print_r($assessmentDetails); exit;

        $assessmentQuestions = AssessmentQuestion::where('quiz_id',$id)->get();            
      
        $assessmentDetailsArray=[];
        $a=0;
        foreach( $assessmentQuestions as  $assessmentQuestion){
            $assessmentDetailsArray[$a]['assessmentQuestion']=$assessmentQuestion;

            $assessmentQuestion = AssessmentAnswer::where('quiz_id',$id)->where('user_id',$uid)->first();  
            
            $assessmentDetailsArray[$a]['assessmentAnswer']=$assessmentQuestion;


            $a++;
        }

        return view('instructor.assessmentReview', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('assessmentDetailsArray','assessmentDetails','id','uid'));
    }

    
    public function saveassessmentReview(Request $request){

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
                
                /*

                AssessmentAnswer::create([
                    'user_id' => $UserID,
                    'quiz_id' => $AssessmentID,
                    'question_id' => $questionIDs[$index],
                    'question_name' => $questionIDs[$index],
                    'answer' => $questionAnswers[$index],
                    'status' => 1,
                    'created_by' => Auth::user()->id,
                ]); 

                */

                $affectedRows = AssessmentAnswer::where('user_id', $UserID)
                ->where('quiz_id', $AssessmentID)
                ->where('question_id', $questionIDs[$index])
                ->update([
                    'review_mark' => $questionAnswers[$index],
                    'review_status' => 1,
                    'review_user' => Auth::user()->id, 
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

    public function approveassessmentReview(Request $request){

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

        $course_id = $CourseID;
        $user_id = $UserID;
        $quiz_id = $AssessmentID;

        /*
        $AssessmentAnswer = AssessmentAnswer::where('user_id', $user_id)->where('quiz_id', $quiz_id)->delete();
        */

        $AssessmentAnswer = AssessmentAnswer::where('user_id', $user_id)
                ->where('quiz_id', $quiz_id)
                ->update([
                    'review_status' => 2 
                ]);

        $certificate_id = time();        
        
        $LearnerCertificate = new LearnerCertificate([
            'certificate_id' => $certificate_id,
            'course_id' => $course_id,
            'user_id' => $user_id,
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        $LearnerCertificate->save();
                
        $CertificateDetails = new CertificateDetails([
            'certificate_id' => $LearnerCertificate->id,
            'grade' => 'A',
            'course_start_date' => now()->subDays(30),
            'course_end_date' => now(),
            'certificate_validity' => 365,
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        $CertificateDetails->save();


        if($LearnerCertificate){
            return response()->json(['success' => 'Successfully Submitted', 'Data' => $request->questionIDs]);

        }else{
            return response()->json(['error' => 'issues on submit']);
        }
        print_r($request); exit;
    }

    public function deleteassessmentReview(Request $request){

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

        $user_id = $UserID;
        $quiz_id = $AssessmentID;

        $AssessmentAnswer = AssessmentAnswer::where('user_id', $user_id)->where('quiz_id', $quiz_id)->delete();
        
        if($AssessmentAnswer){
            return response()->json(['success' => 'Successfully Submitted', 'Data' => $request->questionIDs]);

        }else{
            return response()->json(['error' => 'issues on submit']);
        }
        print_r($request); exit;
    }
    
    public function assessmentReviewedInstructor()
    {
        $authusersRole = Auth::user()->user_group_id;
        
        $userIdAuth = Auth::user()->email;
         
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();

        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();

        $Entrollments = [];
        $batchs = Batch::where('status', '!=', 0)->get();
        $sessions = Session::where('status', '!=', 0)->get();

        $AssessmentReviewQuiz = AssessmentAnswer::where('review_status',1)->select('user_id', 'quiz_id')->distinct()->get();
       
        $AssessmentReviewArray=[];
        $a=0;
        foreach($AssessmentReviewQuiz as $AssessmentReviewQuizDetail){

            
           $UserDetail = UserAccountBasicInfo::where('status', '=', 1)
           ->where('user_id',$AssessmentReviewQuizDetail->user_id)->first();
       
            $AssessmentReviewArray[$a]['userInfo']=$UserDetail;
            
           $AssessmentDetail = AssessmentDetails::with([
            'courseBasicInfo',
            'courseLessonBasicInfo'
            ])->where('status', '=', 1)->where('id',$AssessmentReviewQuizDetail->quiz_id)->first();
       

            $AssessmentReviewArray[$a]['assessmentInfo']=$AssessmentDetail;
            
            $a++;
        }



        //print_r($AssessmentReviewArray); exit;
 

        return view('instructor.assessmentReviewedInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Entrollments', 'courses', 'usergroups', 'users', 'sessions','AssessmentReviewArray'));
 
    }    


    public function assessmentReviewed($id,$uid)
    {
        //$lid = $_REQUEST['lid'];

        $totalMark = 0;
        $totalScored = 0;
        
        $assessmentDetails = AssessmentDetails::with([
            'courseBasicInfo',
            'assessmentSetting',
            'assessmentQuestions',
            'assessmentAnswers' => function ($query) use ($id, $uid) {
                $query->where('quiz_id', $id)->where('user_id',$uid);
            }
            ])->where('id',$id)->first();            
      
        $totalMark = $assessmentDetails->assessmentSetting->section1_total + $assessmentDetails->assessmentSetting->section2_total;
        
        $assessmentQuestions = AssessmentQuestion::where('quiz_id',$id)->get();            
      
        $assessmentDetailsArray=[];
        $a=0;
        foreach( $assessmentQuestions as  $assessmentQuestion){
            $assessmentDetailsArray[$a]['assessmentQuestion']=$assessmentQuestion;

            $assessmentQuestion = AssessmentAnswer::where('quiz_id',$id)->where('user_id',$uid)->first();  
            
            $assessmentDetailsArray[$a]['assessmentAnswer']=$assessmentQuestion;

            $a++;
        }

        foreach( $assessmentDetails->assessmentAnswers as  $assessmentAnswer){

            $totalScored = $totalScored + $assessmentAnswer->review_mark;
            //print_r($assessmentAnswer->review_mark);
        }

        return view('instructor.assessmentReviewed', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('assessmentDetailsArray','assessmentDetails','totalMark','totalScored','id','uid'));
    }

}
