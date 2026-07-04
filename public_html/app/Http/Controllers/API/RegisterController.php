<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Profile;
use App\Mail\VerificationEmail;
use App\Mail\InvalidIdProofEmail;
use Illuminate\Support\Facades\Mail;
use Log;
use App\Models\CourseBasicInfo;
use App\Models\UserAccountBasicInfo;
use App\Models\Entrollment;
use App\Models\CourseCategory;
use App\Models\UserGroup;
use App\Models\CourseLessonBasicInfo;
use App\Models\LeadPayment;

use App\Models\AssessmentDetails;
use App\Models\AssessmentAnswer;

class RegisterController extends BaseController

{
    /**
    * Register api
    *
    * @return \Illuminate\Http\Response
    */

    /** get all users */
    public function index()
    {
        $users = User::all();
        return $this->sendResponse($users, 'Displaying all users data');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'c_password' => 'required|same:password',
            'user_group_id' => 'nullable|exists:user_groups,id',
            'phone_code' => 'nullable|integer',
            'phone_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                'Validation failed. Please check your input.',
                $validator->errors(),
                422
            );
        }

        try {
            $user = User::create(User::attributesForRegistration($request->only([
                'name',
                'email',
                'password',
                'user_group_id',
                'phone_code',
                'phone_number',
            ])));
        } catch (\Throwable $e) {
            report($e);

            return $this->sendError(
                'Registration could not be completed. Please try again later.',
                config('app.debug') ? ['error' => $e->getMessage()] : (object) [],
                500
            );
        }

        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['name'] = $user->name;
        $success['user_group_id'] = $user->user_group_id;

        return $this->sendResponse($success, 'Registration successful.');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                'Validation failed. Please check your input.',
                $validator->errors(),
                422
            );
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;
            $success['user_group_id'] = $user->user_group_id;

            return $this->sendResponse($success, 'Login successful.');
        }

        return $this->sendError(
            'Invalid email or password.',
            ['error' => 'The credentials you entered are incorrect.'],
            401
        );
    }
    public function AdminRegister(Request $request)
    {
        if ($request->hasFile('logo')) {

            $photo = $request->file('logo');
            $photoname = time() . '.' . $photo->extension();
            $photo->move(public_path('uploads'), $photoname);
            $photonamepath = $photoname;
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
        return redirect()->intended('/');
    }
    public function dashboard()
    {
        $companies = UserGroup::where('role','Company')->get();

        $learners = UserGroup::where('name','Learner')->get()->pluck('id')->toArray();

        $instructors = UserGroup::where('role','Instructor')->get()->pluck('id')->toArray();
        
        $learnerUser = User::whereIn('user_group_id',$learners)->where('status',1)->get();
        $instructorUser = User::whereIn('user_group_id',$instructors)->where('status',1)->get();


        $category = CourseCategory::where('status',1)->get();
        $course = CourseBasicInfo::where('status',1)->get();
        $unit = CourseLessonBasicInfo::where('status',1)->get();

        $CoursesWithoutInstructor = DB::table('course_basic_infos as c')
        ->leftJoin('course_batches as b', 'c.id', '=', 'b.course_id')
        ->whereNull('b.course_id')
        ->count();
 
 
        $InstructorsWithoutCourse = DB::table('users as u')
        ->leftJoin('course_batches as b', 'u.id', '=', 'b.instructor_id')
        ->where('u.user_group_id', 3)
        ->whereNull('b.instructor_id')
        ->count();


        return view('admin.dashboard', ['title' => 'Dashboard LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('companies','learnerUser','instructorUser','category','course','unit','CoursesWithoutInstructor','InstructorsWithoutCourse'));
    }
    public function learner()
    {
		//dd(Auth::user()->id);
		        $payments = LeadPayment::where('user_id',Auth::user()->id)->where('payment_status', '=',0)
->get();
//dd($payments);
        $all_result = DB::table('enrollments')
            ->select('course_id')
            ->distinct()
            ->where('user_id', Auth::user()->id)
            ->get();
            
        $all_result_count = count($all_result);   

        $complete_result = DB::table('course_completed_trackings')
            ->select('course_id')
            ->distinct()
            ->where('user_id', Auth::user()->id)
            ->where('complete', 1)
            ->get();

        $complete_result_count = count($complete_result);    

        foreach($complete_result as $complete_result_info) {
            //print_r($complete_result_info);

            $complete_result_check = DB::table('course_completed_trackings')
            ->select('course_id')
            ->distinct()
            ->where('course_id',$complete_result_info->course_id)
            ->where('user_id', Auth::user()->id)
            ->where('complete', 0)
            ->get();

            if(count($complete_result_check)>0){
                $complete_result_count--;
            }


        }

        /* Assessment::start  */

        $my_course_ids = Entrollment::where('user_id',Auth::user()->id)->where('enrolled','1')->pluck('course_id')->toArray();
        $all_assessment_result = AssessmentDetails::with(['courseBasicInfo','courseLessonBasicInfo','assessmentQuestions'])->whereIn('course_id',$my_course_ids)->where('status',true)->get();
        $all_assessment_result_count = count($all_assessment_result);   

        
        $my_quiz_ids = AssessmentDetails::whereIn('course_id',$my_course_ids)->where('status',true)->pluck('id')->toArray();
         
        $complete_assessment_result_count = 0;
        if($my_quiz_ids){

            $complete_assessment_result = AssessmentAnswer::whereIn('quiz_id',$my_quiz_ids)->where('user_id',Auth::user()->id)
            ->distinct('quiz_id')
    ->count('quiz_id');
            if($complete_assessment_result){
                 $complete_assessment_result_count = $complete_assessment_result;    
            }
    
        } 

        //$pending_assessment_result_count = $all_assessment_result_count - $complete_assessment_result_count;


        /* Assessment::end */

        $CourseBasicInfo = CourseBasicInfo::with(['courseCategory','courseLessonBasicInfo'])
        ->where('status',true)
        ->latest() 
        ->take(5)
        ->get();

        $user = Auth::user();
        $my_course_ids = Entrollment::where('user_id',$user->id )->where('enrolled','1')->pluck('course_id')->toArray();

        $MyCourseBasicInfo = [];
        $MyCourseBasicInfo = CourseBasicInfo::whereIn('id', $my_course_ids)
        ->latest() 
        ->take(5)
        ->get();

        $user = Auth::user();
        $my_course_ids = Entrollment::where('user_id',$user->id )->where('enrolled','1')->pluck('course_id')->toArray();

        $courseProgressInfo = [];
        $courseProgressInfos = CourseBasicInfo::with(['courseCategory'])
        ->whereIn('id', $my_course_ids)
        ->latest() 
        ->take(3)
        ->get();
        foreach($courseProgressInfos as $item){
            $courseProgressInfo[] = $item->calculateCompletionPercentage(Auth::user()->id);
        }
            
        $categories = CourseCategory::all();
       
                 
                 return view('learner.dashboard', ['title' => 'Learner LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('all_result','complete_result','all_result_count','complete_result_count','CourseBasicInfo','MyCourseBasicInfo','categories','courseProgressInfo','all_assessment_result_count','complete_assessment_result_count','payments'));
    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); 

        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect('/');
    }
    public function profileRegister()
    {
        return view('admin.admin.register', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb']);
    }
    public function country(Request $request)
    {
        $countries = Country::all();
        $successtest  ="";
        $country = $request->input('countryID');
        $states = State::where('country_id', $country)->get();

        return $states;
    }
    //////////////////////////////////////////////////////////////Profile verfication /////////////////////////////////////////////////////////////////////////
    public function verify($id,$verifyId)
    {
        $user = User::where('id',$id)->first();
         $profile = UserAccountBasicInfo::where('user_id',$id)->first();
       
        if(!isset($profile)){
            return redirect()->route('edituseraccount', ['id' => $id])->with('error', 'Please complete profile details!');
        }
        elseif($verifyId == 1)
        {
            $length = 12;
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_';

            $password = substr(str_shuffle($characters), 0, $length);

            $userData = [
                'name' => $profile->first_name,
                'email' => $user->email,
                'role' => 2,
                'password' => $password,
            ];
            
            $profile->verficationstatus = 1;
            $profile->mailStatus = 1;
            $profile->save();


            $user->password = $password;
            $user->save();

            //Mail::to($profile->email)->send(new VerificationEmail($userData));
            try {
            
                Mail::to($user->email)->send(new VerificationEmail($userData));
                return redirect()->back()->with('success', 'Verification email sent!');
                
            } catch (\Exception $e) {
\Log::error('Mail send failed: ' . $e->getMessage()); 
                return redirect()->back()->with('error', 'Verification email not sent!, Try Again'.$e->getMessage());
            }

           
        }
        elseif($verifyId == 2)
        {
            
            $profile->verficationstatus = 2;
            $profile->mailStatus = 1;
            $profile->save();

            $userData = [
                'name' => $profile->first_name,
                'email' => $user->email,
                'error' => "Your ID proof is not valid. Not eligibility for registration."
            ];
            
            //Mail::to($profile->email)->send(new InvalidIdProofEmail($userData));
        
            try {
            
                Mail::to($user->email)->send(new InvalidIdProofEmail($userData));
                return redirect()->back()->with('success', 'Required Information Request email sent!');
                
            } catch (\Exception $e) {

                return redirect()->back()->with('error', 'Required Information Request email not sent!, Try Again');
            }
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function generateRandomPassword($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_';
        return substr(str_shuffle($characters), 0, $length);
    }
}