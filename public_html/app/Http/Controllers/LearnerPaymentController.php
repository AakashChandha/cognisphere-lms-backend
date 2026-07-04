<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Learnerfeessummary;
use App\Models\Learnerfeestransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\LearnerCertificate;
use App\Models\CertificateDetails;
use App\Models\CourseBasicInfo;
use App\Models\UserGroup;
use App\Models\Entrollment;
use App\Models\Country;
use App\Models\State;
use App\Models\Batch;
use App\Models\Session;
use App\Models\UserAccountBasicInfo;
use App\Models\CourseCompleted;
use App\Models\Content;
use App\Models\CourseTracking;
use App\Models\Payment;
use App\Models\LeadPayment;

use App\Models\MapCourse;
use App\Mail\EnrollmentEmail;
use Illuminate\Support\Facades\Mail;

class LearnerPaymentController extends Controller
{

    public function registration()
    {
        $Countrys = Country::all();


        return view('learnerManagement.register', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('Countrys'));
    }
    public function country(Request $request)
    {
        $countries = Country::all();
        $successtest  ="";
        $country = $request->input('countryID');
        $states = State::where('country_id', $country)->get();

        return $states;
    }
    //
    public function index()
    {
        $authuser = Auth::user();
        $fesssummarys = Learnerfeessummary::with(['user','courseBasicInfo'])->get();
        $users = collect();
        foreach ($fesssummarys as $summary) {
            $user = User::find($summary->user_id);
            if ($user) {
                $users->push($user);
            }
        }

        $fessTransactions = "";
        $viewModelTable = false;
        $individualfesssummarys = "";
        $individualModel = false;
        
        $courses = CourseBasicInfo::where('status',true)->get();
        $users = User::where('status',true)->get();
        return view('learnerManagement.index', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('individualfesssummarys','fesssummarys', 'users', 'fessTransactions', 'viewModelTable','individualModel','individualfesssummarys','courses'));
    }

    public function onlineEnrollments(Request $request)
    {
        $authuser = Auth::user();
        $paymentsInfo = Payment::with(['user','courseBasicInfo'])->where('status','success')->orderBy('id','desc')->get();
        $payments=[];
        foreach($paymentsInfo as $payment){
            $EntrollmentInfo = Entrollment::where('user_id',$payment->user_id)->where('course_id',$payment->course_id)->first();
            $payment['enrollmentInfo']=$EntrollmentInfo;
            $payments[]=$payment;
            //$payments[]['user']=$payment->user;
            //$payments[]['enrollmentInfo']=$EntrollmentInfo;
        }
        //print_r($payments);exit;
        $fessTransactions = "";
        $viewModelTable = false;
        $individualfesssummarys = "";
        $individualModel = false;
        
        return view('learnerManagement.online_enrollments', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('payments'));
    }
  public function pendingpayment()
    {
	 $payments = LeadPayment::where('user_id',Auth::user()->id)->where('payment_status', '=',0)
->get();
	        return view('learner.mypendingpayments', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('payments'));
	
	}
    public function makepayment(Request $request)
    {
        $validatedData = $request->validate([
            'date_of_payment' => 'string',
            'mode_of_payment' => 'string',
            'user_id' => 'integer',
            'course_id' => 'integer',
            'transaction_id' => '',
            'paid_amount' => 'integer',
        ]);

        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['status'] = 1;

        $learnerTransaction = Learnerfeestransaction::create($validatedData);
        $learnerSummary = Learnerfeessummary::where('user_id', $request->user_id)->where('course_id', $request->course_id)->first();
        if(isset($learnerSummary)){
            $learnerSummary->last_paid_fee = $request->paid_amount;
            $learnerSummary->balance_fee = $learnerSummary->balance_fee - $request->paid_amount;
            $learnerSummary->save();
        }else{
            $course = CourseBasicInfo::find($request->course_id);
            $learnerSummary = Learnerfeessummary::create([
                'created_by' => Auth::user()->id,
                'user_id' => $request->user_id,
                'course_id' => $request->course_id,
                'actual_fee' => $course->course_price,
                'discount' => 0,
                'paid_fee' => $request->paid_amount,
                'balance_fee' => ($course->course_price - $request->paid_amount),
                'last_paid_fee' => $request->paid_amount,
            ]);
        }
        return redirect()->route('learnerfeessummary')->with('success', 'Payment Successfully');
        //return redirect()->back()->with('error', 'Payment module is under maintenance');
    }

    public function ViewTransction($id,$course_id)
    {
        
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $usersAccounts = User::with(['userAccountBasicInfo'])->get();
        
        
        $fesssummarys = Learnerfeessummary::where('user_id', $id)->get();
        $users = User::all();
        $fessTransactions = Learnerfeestransaction::with(['user','courseBasicInfo'])->where('user_id', $id)->where('course_id',$course_id)->get();
        $viewModelTable = true;
        $individualfesssummarys = "";
        $individualModel = false;
        return view('learnerManagement.index', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('individualfesssummarys','fesssummarys', 'users', 'fessTransactions', 'viewModelTable','individualModel','courses','usergroups','usersAccounts'));

    }

    public function payIndividual($id, $course_id)
    {
        
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $usersAccounts = User::with(['userAccountBasicInfo'])->get();
        
        $authuser = Auth::user();
        $fesssummarys = Learnerfeessummary::where('user_id', $id)->get();
        $users = User::all();
        $fessTransactions = "";
        $viewModelTable = false;
        $individualfesssummarys = Learnerfeessummary::with(['user'])->where('user_id', $id)
            ->where('course_id', $course_id)
            ->first();
        $individualModel = true;
        return view('learnerManagement.index', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('individualfesssummarys','fesssummarys', 'users', 'fessTransactions', 'viewModelTable','individualModel','courses','usergroups','usersAccounts'));
    }
    public function makepaymentindividual(Request $request)
    {
        $data = $request->all();

        $data['created_by'] = auth()->user()->id;
        $data['status'] = 1;

        $learnerTransaction = Learnerfeestransaction::create($data);

        $learnerSummary = Learnerfeessummary::where('user_id', $request->user_id)->where('course_id', $request->course_id)->first();

        $learnerSummary->last_paid_fee = $request->paid_amount;

        $paidblanace =  $learnerSummary->balance_fee - $request->paid_amount;

        $learnerSummary->balance_fee = $paidblanace;
       
        $learnerSummary->save();
        return redirect()->route('learnerfeessummary')->with('success', 'Payment Successfully');

    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Leaner certificate Functions 
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function learnercertificates()
    {
        $authuser = Auth::user();
        //$learnerCertificates = LearnerCertificate::all();
        
        $learnerCertificates = LearnerCertificate::with(['user','courseBasicInfo','certificate','courseCategoryInfo'])->get();
       

        $courses = CourseBasicInfo::all();      
        $usersAccounts = User::with(['userAccountBasicInfo'])->get();
        //print_r($learnerCertificates);exit;

        return view('learnerManagement.learnercertificates', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('learnerCertificates','usersAccounts','courses'));
    }

    
    public function approvelearnercertificate($id)
    {
        $authuser = Auth::user();
        
        //$learnerCertificates = LearnerCertificate::with(['user','courseBasicInfo','certificate'])->get();

        $learnerCertificates = learnerCertificate::where('id', $id)
        ->update([
            'status' => 2 
        ]);

        return redirect()->route('learnercertificates')->with('success', 'Certificate Successfully Approved');
    }
    
    public function createcertificate(Request $request)
    {
        $data = $request->all();
        $data['created_by'] = auth()->user()->id;
        $data['status'] = 1;

        /*
        $learnerCertificate = CertificateDetails::create($data);
        */

        $course_id = $data['course_id'];
        $user_id = $data['user_id'];         
        $certificate_id = $data['certificate_id'];    
        $grade = $data['grade'];    
        $course_start_date = $data['course_start_date'];
        $course_end_date = $data['course_end_date'];
        $certificate_validity = $data['certificate_validity'];        
        
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
            'grade' => $grade,
            'course_start_date' => $course_start_date,
            'course_end_date' => $course_end_date,
            'certificate_validity' => $certificate_validity,
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        $CertificateDetails->save();


        return redirect()->route('learnercertificates')->with('success', 'Certificate Successfully Created');
    }

    public function my_certificate()
    {
        $authuser = Auth::user();
        //$learnerCertificates = LearnerCertificate::all(); 
        $learnerCertificates = LearnerCertificate::with(['user','courseBasicInfo','certificate','courseCategoryInfo'])->where('user_id',$authuser->id)->get();
       

        $courses = CourseBasicInfo::all();      
        $usersAccounts = User::with(['userAccountBasicInfo'])->get();
        //print_r($learnerCertificates);exit;

        return view('learnerManagement.my_certificate', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('learnerCertificates','usersAccounts','courses'));
    }

    public function Enrollment()
    {
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $usersAccounts = User::with(['userAccountBasicInfo'])->get();
        
        $entrollments = Entrollment::with(['userGroup','user','user.userAccountBasicInfo','courseBasicInfo','batch','sessionInfo'])->where('enrolled', '=', 0)->get();
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
        return view('learnerManagement.entrollment', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('batchs','courses','usergroups','usersAccounts','entrollments'));
    }

    public function EnrollmentCompleted()
    {
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $usersAccounts = User::with(['userAccountBasicInfo'])->get();
        
        $entrollments = Entrollment::with(['userGroup','user','user.userAccountBasicInfo','courseBasicInfo','batch'])->where('enrolled', '=', 1)->get();
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
        return view('learnerManagement.entrollmentcompleted', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('batchs','courses','usergroups','usersAccounts','entrollments'));
    }

    public function addentrolluser(Request $request)
    {
        $code = mt_rand(100000, 999999);

        $data = $request->all();
        $data['created_by'] = auth()->user()->id;
        $data['enrolled'] = 0;
        $data['course_type'] = "offline";
        $data['status'] = 1;

        $data['code'] = $code;


        $checkMapper = MapCourse::where('course_id',$data['course_id'])->count();
        if($checkMapper>0){

        $checkEnroll = Entrollment::where('course_id',$data['course_id'])
        ->where('batch_id',$data['batch_id'])
        ->where('session_id',$data['session_id'])
        ->where('user_group_id',$data['user_group_id'])        
        ->where('user_id',$data['user_id'])        
        ->count();    
        if($checkEnroll>0){       
            return redirect()->route('Enrollment')->with('error', 'Enrollment Already Exist');
        }
        $Entrollments = Entrollment::create($data);
        $lastInsertId = $Entrollments->id;


        $entrollment = Entrollment::with(['userGroup','user','user.userAccountBasicInfo','courseBasicInfo','batch','sessionInfo'])->where('id', $lastInsertId)->first();
       
        $data['entrollment']=$entrollment;
        try {
             
            Mail::to('kannan.karuppiah@gmail.com')->send(new EnrollmentEmail($data));
            Mail::to('info@cognispheremc.com')->send(new EnrollmentEmail($data));
            
        } catch (\Exception $e) {

        return redirect()->route('viewuseraccount')->with('error', 'User Account Created Successfully. Issue on Email Server.');

        }

        return redirect()->route('Enrollment')->with('success', 'Enrollment Successfully Created');
        } else {
            
        return redirect()->route('Enrollment')->with('error', 'Enrollment failure, Please map instructor');
        }
    }
    public function StoreEntroll(Request $request)
    {
        $enrollmentIds = $request->input('enrollmentIds'); 
        $OTP = $request->input('otp');
       
        if($enrollmentIds==''){
            return redirect()->route('Enrollment')->with('error', 'Please select minimum One User to Enrolled');
        }
 
        $entrollment = Entrollment::where('id',$enrollmentIds)->where('code',$OTP)->first();
        if ($entrollment) {
            $entrollment->enrolled = 1;
            $entrollment->save();
        } else {
            return redirect()->route('Enrollment')->with('error', 'Invalid OTP,Please check and update');
      
        }

        //print_r( $entrollment );exit;
        /*
        //echo $entrollmentIDS = explode(',', $enrollmentIds); 
        foreach ($entrollmentIDS as $entrollmentID) {
            $entrollment = Entrollment::find($entrollmentID);
            if ($entrollment) {
                $entrollment->enrolled = 1;
                $entrollment->save();
            }
        }
        */
        return redirect()->route('Enrollment')->with('success', 'Enrolled Successfully Created');
    }

    public function mycourses()
    {
        $user = Auth::user();
        $my_course_ids = Entrollment::where('user_id',$user->id )->where('enrolled','1')->pluck('course_id')->toArray();

        $courseBasicInfo = [];
        $courseBasicInfos = CourseBasicInfo::whereIn('id', $my_course_ids)->get();
        foreach($courseBasicInfos as $item){
            $courseBasicInfo[] = $item->calculateCompletionPercentage(Auth::user()->id);
        }
        $course_filter_type = "my_courses";
        $page_title = "My Courses";
        return view('learner.mycourses', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact(['courseBasicInfo', 'course_filter_type','page_title']));
    }

    public function currentCourses()
    {
        $user = Auth::user();
        $my_course_ids = Entrollment::where('user_id',$user->id )->where('enrolled','1')->pluck('course_id')->toArray();
        
        $courseBasicInfo = [];
        $courseBasicInfos = CourseBasicInfo::whereIn('id', $my_course_ids)->get();
        foreach($courseBasicInfos as $item){
             $item = $item->calculateCompletionPercentage(Auth::user()->id);
             if($item->completed_percentage < 100){
                $courseBasicInfo[] = $item;
             }
        }
        $course_filter_type = "current";
        $page_title = "Current Courses";
        return view('learner.mycourses', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact(['courseBasicInfo', 'course_filter_type','page_title']));
    }

    public function completedCourses()
    {
        $user = Auth::user();
        $my_course_ids = Entrollment::where('user_id',$user->id )->where('enrolled','1')->pluck('course_id')->toArray();
        
        $courseBasicInfo = [];
        $courseBasicInfos = CourseBasicInfo::whereIn('id', $my_course_ids)->get();
        foreach($courseBasicInfos as $item){
            $item = $item->calculateCompletionPercentage(Auth::user()->id);
            if($item->completed_percentage >= 100){
               $courseBasicInfo[] = $item;
            }
        }
        $course_filter_type = "completed";
        $page_title = "Completed Courses";
        return view('learner.mycourses', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact(['courseBasicInfo', 'course_filter_type','page_title']));
    }

    public function profileview()
    {
        $user = User::with(['userAccountBasicInfo'])->where('id', Auth::user()->id)->first();
        //print_r($user);exit;
        //$ProfileInfo = UserAccountBasicInfo::where('user_id', Auth::user()->id)->first();
        return view('learner.profileview', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('user'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        if(isset($user)){
            $user->name = $request->first_name.' '.$request->last_name;
            $user->phone_number = $request->phone;
            $user->save();
            $userAccountBasicInfo = UserAccountBasicInfo::where('user_id', $user->id)->first();
            if(!isset($userAccountBasicInfo)){
                $userAccountBasicInfo = UserAccountBasicInfo::create(['user_id'=> $user->id]);
            }
            $userAccountBasicInfo->first_name = $request->first_name;
            $userAccountBasicInfo->last_name = $request->last_name;
            $userAccountBasicInfo->address = $request->address;
            $userAccountBasicInfo->save();
            return redirect()->route('profileview')->with('success','Profile has been updated');
        }
        return redirect()->route('profileview')->with('error','Invalid User');
    }

    public function  changepassword(){
        $user = User::with(['userAccountBasicInfo'])->where('id', Auth::user()->id)->first();
        //$ProfileInfo = UserAccountBasicInfo::where('user_id', Auth::user()->id)->first();
        return view('learner.changepassword', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        if(isset($user)){
            $password = $request->password;
            $confirmpassword = $request->confirmpassword;
            if($password==$confirmpassword) {
                $user->password = $request->password;
                $user->save();
                return redirect()->route('changepassword')->with('success','Password has been updated');
            } else {
                return redirect()->route('changepassword')->with('error','Password and Confirm Password Not Match');

            }
        }
        return redirect()->route('changepassword')->with('error','Invalid User');
    }


    public function payfee()
    {
        $authuser = Auth::user();

        $UserAccount = User::where('email', $authuser->email)->first();

        $count = Learnerfeessummary::where('user_id', $UserAccount->id)->count();

        if($count == 1)
        {
            $dummyData = [
                'user_id' => $UserAccount->id,
                'course_id' => 1,
                'actual_fee' => 4999,
                'discount' => 100,
                'paid_fee' => 4899,
                'balance_fee' => 4899,
                'last_paid_fee' => 0,
                'status' => 1,
                'created_by' => $authuser->id,
            ];

            // Create a new payment record with dummy data
            Learnerfeessummary::create($dummyData);
        }

        $individualfesssummarys = Learnerfeessummary::where('learner_fees_summaries.user_id', $UserAccount->id)
            ->where('learner_fees_summaries.course_id', 1)
            ->join('useraccount', 'useraccount.id', '=', 'learner_fees_summaries.user_id')
            ->join('course_basic_infos', 'course_basic_infos.id', '=', 'learner_fees_summaries.course_id')
            ->select('learner_fees_summaries.*', 'useraccount.*','course_basic_infos.id', 'course_basic_infos.course_name', 'course_basic_infos.id as course_primary_id')
            ->first();
        
        return view('learner.payment.index', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('individualfesssummarys'));
    }
    public function Studentsmakepayment(Request $request)
    {
        $data = $request->all();
        $data['created_by'] = auth()->user()->id;
        $data['status'] = 1;

        $learnerTransaction = Learnerfeestransaction::create($data);

        $learnerSummary = Learnerfeessummary::where('user_id', $request->user_id)->where('course_id', $request->course_id)->first();

        $learnerSummary->last_paid_fee = $request->paid_amount;

        $paidblanace =  $learnerSummary->balance_fee - $request->paid_amount;

        $learnerSummary->balance_fee = $paidblanace;
       
        $learnerSummary->save();
        return redirect()->route('myTransaction')->with('success', 'Payment Successfully');
    }

    public function myTransaction()
    {
        $authuser = Auth::user();
        $UserAccount = User::where('email', $authuser->email)->first();
        $users = User::all();
    
        $fesssummarys = Learnerfeessummary::where('user_id', $UserAccount->id)->get();
        $ListTransactions = collect(); // Initialize an empty collection
    
        foreach ($fesssummarys as $summary)
        {
            $fessTransactionsGet = Learnerfeestransaction::where('learner_fees_transactions.user_id', $summary->user_id)
                ->where('learner_fees_transactions.course_id', $summary->course_id)
                ->join('useraccount', 'useraccount.id', '=', 'learner_fees_transactions.user_id')
                ->join('course_basic_infos', 'course_basic_infos.id', '=', 'learner_fees_transactions.course_id')
                ->select('learner_fees_transactions.*', 'useraccount.*', 'course_basic_infos.*')
                ->get();
    
            if ($fessTransactionsGet->isNotEmpty())
            {
                $ListTransactions = $ListTransactions->merge($fessTransactionsGet);
            }
        }
        return view('learner.payment.mytransaction', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('fesssummarys', 'users', 'ListTransactions'));
    }

    public function allcourses()
    {
        $auth_user_id = Auth::user()->id;
        $CourseBasicInfo = CourseBasicInfo::with(['courseCategory','courseLessonBasicInfo'])->where('status',true)->get();
        $CourseBasicInfoArray=[];
        foreach($CourseBasicInfo as $courseBasicInfoItem){
            $enrolled_count = Entrollment::where('user_id',$auth_user_id)->where('enrolled','1')->where('course_id',$courseBasicInfoItem->id)->count();
            $courseBasicInfoItem['enrolled'] = ($enrolled_count > 0);
            $enrolled_total_count = Entrollment::where('course_id',$courseBasicInfoItem->id)->where('enrolled','1')->count();
            $courseBasicInfoItem['enrolled_total'] = $enrolled_total_count;
            $batch_total_count = Batch::where('batch_course',$courseBasicInfoItem->id)->where('status',1)->count();
            $courseBasicInfoItem['batch_total'] = $batch_total_count;
            $ins_count = MapCourse::where('course_id',$courseBasicInfoItem->id)->count();
            if($ins_count>0){
            $CourseBasicInfoArray[] = $courseBasicInfoItem;
            }
        }
        /*$CourseBasicInfo = CourseBasicInfo::join('course_categories', 'course_basic_infos.course_category_id', '=', 'course_categories.id')
            ->select('course_basic_infos.id as Maincourse_id', 'course_basic_infos.*', 'course_categories.*')
            ->get();*/
        return view('learner.courses.allcourse', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('CourseBasicInfo','CourseBasicInfoArray'));
    }

    public function coursedetails($id)
    {
        $user = Auth::user();
        $CourseBasicInfoView = CourseBasicInfo::with(['courseCategory','courseLessonBasicInfo'])->find($id);
        $enrolled_count = Entrollment::where('course_id',$id)->count();
 
if($user->user_group_id==2){
    $batch_count = Batch::where('batch_course',$id)->where('status',1)->where('batch_type',1)->count();            
    $batch_Info = Batch::where('batch_course',$id)->where('status',1)->where('batch_type',1)->get();
} else {
    $batch_count = Batch::where('batch_course',$id)->where('status',1)->where('batch_type',3)->count();            
    $batch_Info = Batch::where('batch_course',$id)->where('status',1)->where('batch_type',3)->get();
}
       
        /*$CourseBasicInfoView = CourseBasicInfo::join('course_categories', 'course_basic_infos.course_category_id', '=', 'course_categories.id')
        ->select('course_basic_infos.*', 'course_categories.*')
        ->where('course_basic_infos.id', $id)
        ->first();*/

        $BatchInfoArray=[];
        foreach($batch_Info as $batch_Info_Item){
            /*
            $enrolled_count = Entrollment::where('user_id',$auth_user_id)->where('course_id',$courseBasicInfoItem->id)->count();
            $courseBasicInfoItem['enrolled'] = ($enrolled_count > 0);
            $enrolled_total_count = Entrollment::where('course_id',$courseBasicInfoItem->id)->count();
            $courseBasicInfoItem['enrolled_total'] = $enrolled_total_count;
            $batch_total_count = Batch::where('batch_course',$courseBasicInfoItem->id)->where('status',1)->count();
            $courseBasicInfoItem['batch_total'] = $batch_total_count;
            $ins_count = MapCourse::where('course_id',$courseBasicInfoItem->id)->count();
            if($ins_count>0){
            $BatchInfoArray[] = $courseBasicInfoItem;
            }
            */
            $batch_session = Session::where('batch_id',$batch_Info_Item->id)->where('status',1)->get();
           
            $batch_Info_Item['session'] = $batch_session;


            $BatchInfoArray[] = $batch_Info_Item;
        }

        return view('learner.courses.courseview', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('CourseBasicInfoView','enrolled_count','user','batch_count','batch_Info','BatchInfoArray'));
    }

    public function GetCourseContent(Request $request)
    {
        $authuser = Auth::user();
        $UserId = $authuser->id;

        $courseCompleted = CourseCompleted::where('user_id', $UserId)
            ->where('course_id', $request->CourseId)
            ->where('coureses_topic_completed_percentage', 0)
            ->first();

        if ($courseCompleted) {
            $Lesson_id = $courseCompleted->Lesson_id;

            $courseContentGet = Content::where('course_id', $request->CourseId)
                ->get();

            foreach ($courseContentGet as $content) {
                $existingTracking = CourseTracking::where('user_id', $UserId)
                    ->where('course_id', $content->course_id)
                    ->where('lesson_id', $content->course_lesson_id)
                    ->where('content_id', $content->content_id)
                    ->first();

                if (!$existingTracking) {
                    $Course_tracking = new CourseTracking();
                    $Course_tracking->track_id = $content->course_lesson_id;
                    $Course_tracking->user_id = $UserId;
                    $Course_tracking->course_id = $content->course_id;
                    $Course_tracking->lesson_id = $content->course_lesson_id;
                    $Course_tracking->content_id = $content->content_id;
                    $Course_tracking->status = 0;
                    $Course_tracking->save();
                }
            }
        }
        else
        {
            $courseCompleted = CourseCompleted::where('user_id', $UserId)
            ->where('course_id', $request->CourseId)
            ->first();

            $Lesson_id = $courseCompleted->Lesson_id;

            $courseContentGet = Content::where('course_id', $request->CourseId)
                ->get();

            foreach ($courseContentGet as $content) {
                $existingTracking = CourseTracking::where('user_id', $UserId)
                    ->where('course_id', $content->course_id)
                    ->where('lesson_id', $content->course_lesson_id)
                    ->where('content_id', $content->content_id)
                    ->first();

                if (!$existingTracking) {
                    $Course_tracking = new CourseTracking();
                    $Course_tracking->track_id = $content->course_lesson_id;
                    $Course_tracking->user_id = $UserId;
                    $Course_tracking->course_id = $content->course_id;
                    $Course_tracking->lesson_id = $content->course_lesson_id;
                    $Course_tracking->content_id = $content->content_id;
                    $Course_tracking->status = 0;
                    $Course_tracking->save();
                }
            }
        }

        $CourseTracking = CourseTracking::where('user_id', $UserId)
            ->where('course_id', $request->CourseId)
            ->where('lesson_id', $Lesson_id)
            ->first();
        
        $GetContent = Content::where('course_id', $request->CourseId)
            ->where('course_lesson_id', $Lesson_id)
            ->where('content_id', $CourseTracking->content_id)
            ->first();
        
        return $GetContent;

    }

    public function ContentTrackingShow(Request $request)
    {
        $authuser = Auth::user();
        $UserId = $authuser->id;
        $courseCompleted = CourseCompleted::where('user_id', $UserId)
            ->where('course_id', $request->CourseId)
            ->where('coureses_topic_completed_percentage', 0)
            ->first();
        if($courseCompleted)
        {
            $Lesson_id = $courseCompleted->Lesson_id;
        }
        else
        {
            $courseCompleted = CourseCompleted::where('user_id', $UserId)
            ->where('course_id', $request->CourseId)
            ->first();
            $Lesson_id = $courseCompleted->Lesson_id;
        }
        $CourseTracking = CourseTracking::where('content_id',$request->LastContentId)->first();
        $CourseTracking->status = 1;
        $CourseTracking->save();

        $CourseComplation = CourseCompleted::where('course_id',$request->CourseId)->where('user_id',Auth::user()->id)->first();

        $CourseComplation->count_of_topics_completed = $CourseComplation->count_of_topics_completed + 1;
        $CourseComplation->coureses_topic_completed_percentage = ($CourseComplation->count_of_topics_completed / $CourseComplation->count_of_topics) * 100;
        $CourseComplation->save();

        $CourseTracking = CourseTracking::where('user_id', $UserId)
                                        ->where('course_id', $request->CourseId)
                                        ->where('lesson_id', $Lesson_id)
                                        ->where('status', 0)
                                        ->first();
        $GetContent = Content::where('course_id', $request->CourseId)
            ->where('course_lesson_id', $Lesson_id)
            ->where('content_id', $CourseTracking->content_id)
            ->first();
        // echo "<pre>";
        // print_r($CourseTracking);
        // exit;
        // if($CourseTracking)
        // {
        //     $GetContent = Content::where('course_id', $request->CourseId)
        //     ->where('course_lesson_id', $Lesson_id)
        //     ->where('content_id', $CourseTracking->content_id)
        //     ->first();
        // }
        // else
        // {
        //     $GetContent = Content::where('course_id', $request->CourseId)
        //     ->where('course_lesson_id', $Lesson_id)
        //     ->where('content_id', $CourseTracking->content_id)
        //     ->first();
        // }
        return $GetContent;
    }

    public function getuseraccounts(Request $request)
    {

        $UserAccount = User::where('user_group_id',$request->user_group_id)->get();
        
        return $UserAccount;
    }

    public function getcoursebatches(Request $request)
    {

        $BatchAccount = Batch::where('batch_course',$request->user_group_id)->get();
        
        return $BatchAccount;
    }
    
    public function getbatchcompany(Request $request)
    {
        //$company = UserGroup::where('role', 'Company')->where('username',$request->batchCompany)->first();
         
        $Batch = Batch::where('id',$request->user_group_id)->first();

        $BatchType = $Batch['batch_type'];
        $BatchCompany = $Batch['batch_company'];

        if($BatchType==3){
            $usergroups = UserGroup::where('role', 'Company')->where('id',$BatchCompany)->get();
        } else {
            $usergroups = UserGroup::where('role','!=', 'Company')->get();          
        }
        
        
        return $usergroups;
    }


    
    public function deleteenrollment(Request $request)
    {
        try {
            $userEnrollment = Entrollment::find($request->id);
            $userEnrollment->delete();
            
        } catch (\Exception $e) {
        
            return redirect()->route('Enrollment')->with('error', 'Not able to Delete');
    
        }
        
        return redirect()->route('Enrollment')->with('success', 'User Enrollment Deleted Successfully');
    }
}