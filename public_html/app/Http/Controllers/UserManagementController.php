<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGroup;
use App\Models\User;
use App\Models\UserGroupPermission;
use App\Models\UserAccountPermission;
use App\Models\Batch;
use App\Models\Session;
use App\Models\UserAccountBasicInfo;
use App\Models\Country;
use App\Models\State;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use App\Models\CourseCategory;
use App\Models\CourseBasicInfo;
use App\Models\MapCourse;

class UserManagementController extends Controller
{
    //
    public function index()
    {
        return view('userManagement.index', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb']);
    }
    public function userregistration(Request $request)
    {
        $profileCount = UserAccountBasicInfo::count();
        $prefix = 'REG';
        $timestamp = time();
        $randomNumber = rand(1000, 9999);
        $registrationId = $profileCount + 1;
        $registrationId = $prefix . $timestamp . $randomNumber . $registrationId;

        // Handle the file upload
        if ($request->hasFile('photo')) {

            $photo = $request->file('photo');
            $photoname = time() . '.' . $photo->extension();
            $photo->move(public_path('uploads'), $photoname);
            $photonamepath = $photoname;

            $fullname = $request->input('first_name') . ' '.$request->input('last_name');
            $first_name = $request->input('first_name');
            $last_name = $request->input('last_name');
            $email = $request->input('email');
            $phone_number = $request->input('phone_number');
            $address = $request->input('address');
            $state = $request->input('state');
            $countryGet = Country::where('id', $request->input('country'))->get();

            $country = $countryGet[0]->name;
            $city = $request->input('city');
            $pindcode = $request->input('pincode');
            $lanuageskills = $request->input('languageskills');
            $lanuageskillratio = $request->input('languageskillratio');
            $typeoflearning = $request->input('typeoflearning');
            $idproof = $request->input('idproof');
            $idproofnumber = $request->input('idproofnumber');
            $plan = $request->input('plan');
            $verficationstatus = 0;
            $mailStatus = 0;

            $existingProfile = UserAccountBasicInfo::where('email', $email)->first();
            if ($existingProfile) {
                return redirect()->back()->with('success', 'Instructor added successfully.');
            }

            // Create a new UserGroup instance

            $groupId = UserGroup::where('name', 'Learner')->first()->id;
            $Getgorup = UserGroupPermission::find($groupId);


            $useraccount = User::create([
                'name' => $fullname,
                'user_group_id' => $groupId,
                'password' => "",
                'email' => $email,
            ]);

            UserAccountBasicInfo::create([
                'user_id' => $useraccount->id,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            // Save the UserGroup instance to the database
            $useraccount->save();

            $useraccountID = $useraccount->id;
            $UserAccountPermission = new UserAccountPermission([
                'user_group_id' => $groupId,
                'user_id' => $useraccountID,
                'create_user' => $Getgorup->create_user,
                'edit_user' => $Getgorup->edit_user,
                'view_user' => $Getgorup->view_user,
                'delete_user' => $Getgorup->delete_user,
            ]);

            // Save the UserAccountPermission instance to the database
            $UserAccountPermission->save();
            // Save the file path in the database
            $profile = new UserAccountBasicInfo([
                'user_id' => $useraccountID,
                'fullname' => $fullname,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone_number' => $phone_number,
                'address' => $address,
                'state' => $state,
                'country' => $country,
                'city' => $city,
                'pincode' => $pindcode,
                'languageskills' => $lanuageskills,
                'languageskillratio' => $lanuageskillratio,
                'photo' => $photonamepath,
                'typeoflearning' => $typeoflearning,
                'idproof' => $idproof,
                'idproofnumber' => $idproofnumber,
                'plan' => $plan,
                'registrationId' => $registrationId,
                'verficationstatus' => $verficationstatus,
                'mailStatus' => $mailStatus
            ]);

            $profile->save();
            $entry = 1;
        }
        $successtest = "Profile successfully created! Our team will be in touch with you shortly. Thank you";

        $countries = Country::all();
        $states = "";

        return redirect()->back()->with('success', 'User Group Created Successfully');
    }
    public function newusergroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'role' => 'required|string',
            'username' => 'required|string',
            'organization' => 'required|string',
        ]);

        // Create a new UserGroup instance
        $userGroup = new UserGroup([
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'username' => $request->input('username'),
            'organization' => $request->input('organization'),
        ]);

        // Save the UserGroup instance to the database
        $userGroup->save();
        $groupId = $userGroup->id;

        // Create a new UserGroupPermission instance
        $userGroupPermission = new UserGroupPermission([
            'user_group_id' => $groupId,
            'create_user' => 0,
            'edit_user' => 0,
            'view_user' => 1,
            'delete_user' => 0,
        ]);

        // Save the UserGroupPermission instance to the database
        $userGroupPermission->save();
        return redirect()->back()->with('success', 'User Group Created Successfully');
    }
    public function viewusergroup()
    {
        $usergroups = UserGroup::all();
        $editusergroup = false;
        $Model = false;
        $permissionModel = false;
        return view('userManagement.viewusergroup', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('usergroups', 'editusergroup', 'Model', 'permissionModel'));
    }

    public function editUserGroup($id)
    {
        $editusergroup = UserGroup::find($id);
        $usergroups = UserGroup::all();
        $Model = true;
        return view('userManagement.viewusergroup', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('usergroups', 'editusergroup', 'Model'));
    }
    public function newusergroupupdate(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string',
            'organization' => 'required|string',
        ]);

        // Create a new UserGroup instance
        $userGroup = UserGroup::find($request->id);
        if ($userGroup) {
            $userGroup->update([
                'name' => $request->name,
                'username' => $request->username,
                'organization' => $request->organization,
            ]);
        }
        return redirect()->route('viewusergroup')->with('success', 'User Group Updated Successfully');
    }

    public function deleteusergroup(Request $request)
    {
        $userGroup = UserGroup::find($request->id);
        if ($userGroup) {
            UserGroupPermission::where('user_group_id', $userGroup->id)->delete();
            $userGroup->delete();
        }
        return redirect()->route('viewusergroup')->with('success', 'User Group Deleted Successfully');
    }
    public function viewpermission($id)
    {
        $usergroups = UserGroup::all();
        $editusergroup = false;
        $Model = false;
        $permissionModel = true;
        $usergrouppermissions = UserGroupPermission::where('user_group_id', $id)->first();
        return view('userManagement.viewusergroup', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('usergroups', 'editusergroup', 'Model', 'permissionModel', 'usergrouppermissions'));
    }
    public function editPermission(Request $request)
    {
        // Create a new UserGroup instance
        $userGroup = UserGroupPermission::find($request->id);

        if ($userGroup) {
            $userGroup->create_user = $request->create_user;
            $userGroup->edit_user = $request->edit_user;
            $userGroup->view_user = $request->view_user;
            $userGroup->delete_user = $request->delete_user;
            $userGroup->save();
        }

        $userAccount = UserAccountPermission::where('user_group_id', $request->id)->first();
        if ($userAccount) {
            $userAccount->update([
                'create_user' => $request->create_user,
                'edit_user' => $request->edit_user,
                'view_user' => $request->view_user,
                'delete_user' => $request->delete_user,
            ]);
        }
        return redirect()->route('viewusergroup')->with('success', 'User Group Permission Updated Successfully');
    }


    public function Batch()
    {
        $Company = UserGroup::where('role', 'Company')->get();
        $Category = CourseCategory::all();
        $Courses = CourseBasicInfo::all();

        $batches = Batch::where('status', '!=', 0)->get();
        $batches = Batch::all();
        
        return view('userManagement.Batch', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batches','Company','Category','Courses'));
    }
    public function addbatch(Request $request)
    {
        $category = CourseCategory::where('category_name',$request->batchCategory)->first();
        if($category){
            $category_id=$category['id'];
        } else {
            $category_id=0;
        }
        $course = CourseBasicInfo::where('course_id',$request->batchCourse)->first();
        if($course){
            $course_id=$course['id'];
        } else {
            $course_id=0;
        }
        if($request->batchCompany){
            $company = UserGroup::where('role', 'Company')->where('username',$request->batchCompany)->first();
            if($company){
                $company_id=$company['id'];
            } else {
                $company_id=0;
            }
        } else {
            $company_id=0;
        }
       
        $batchCount = Batch::where('batch_name', $request->batch_name)->count();
        if($batchCount>0){
            return redirect()->route('Batch')->with('error', 'Batch Name Already created, Please Check');
        }
        // Create a new UserGroup instance
        $batch = new Batch([
            'batch_type' => $request->input('batchType'),
            'batch_name' => $request->input('batch_name'),
            'batch_size' => $request->input('batch_size'),
            'batch_category' => $category_id,
            'batch_course' => $course_id,
            'batch_company' => $company_id,
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        // Save the UserGroup instance to the database
        $batch->save();
        return redirect()->route('Batch')->with('success', 'Create batch Successfully');
    }
    public function updatebatch(Request $request)
    {
        $batchCount = Batch::where('batch_name', $request->batch_name)->where('id','!=',$request->id)->count();
        if($batchCount>0){
            
        return redirect()->route('Batch')->with('error', 'Batch Name Already Available with Another One, Please Check');
        }
        // Create a new UserGroup instance
        $batch = Batch::find($request->id);
        if ($batch) {
            $batch->update([
                'batch_name' => $request->batch_name,
                'batch_size' => $request->batch_size,
            ]);
        }
        return redirect()->route('Batch')->with('success', 'Batch Updated Successfully');
    }
    public function editbatch($id)
    {
        $Company = UserGroup::where('role', 'Company')->get();
        $Category = CourseCategory::all();
        $Courses = CourseBasicInfo::all();
        // Create a new UserGroup instance
        $batch = Batch::with(['batchCategory','batchCourse','batchCompany'])->find($id);

        $editModel = true;
        return view('userManagement.Batch', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batch', 'editModel', 'Company', 'Category', 'Courses'));
    }

    public function deletebatch(Request $request)
    {
        
        $batch = Batch::find($request->id);
        if ($batch) {
            $batch->status = 0;
            $batch->save();
        }
        
        //Batch::where('id',$request->id)->delete();
        return redirect()->route('Batch')->with('success', 'Batch Deleted Successfully');
    }

    public function ChangeStatusBatch(Request $request)
    {
    
        $batch = Batch::where('id', $request->batch_id)->first();
        $batch->status = $request->status;
        $batch->save();
         
        return response()->json(['success' => 'Status change successfully.']);
    }

    
    public function  getCategoryCourse($Id)
    {
        $categoriesInfos = CourseCategory::where('category_name',$Id)->first()->id;
        $courseCageoriesInfos = CourseBasicInfo::where('course_category_id',$categoriesInfos)->get();
        /*
        $courseId=CourseBasicInfo::select('course_id')->where('id',$Id)->first()->course_id;
        $categoriesInfos = CourseBasicInfo::select('course_category_id')->where('course_id', $courseId)->pluck('course_category_id')->toArray();
        $courseCageoriesInfos=CourseCategory::whereIn('id',$categoriesInfos)->get(); 
        // return 'Text';
        */
         return response()->json($courseCageoriesInfos); 
    }

    
    public function  getCourseInfo($Id)
    {
        $courseId = Batch::where('id', $Id)->where('status', 1)->first()->batch_course;
        $courseCageoriesInfos = CourseBasicInfo::where('id',$courseId)->first()->duration;
        /*
        $courseId=CourseBasicInfo::select('course_id')->where('id',$Id)->first()->course_id;
        $categoriesInfos = CourseBasicInfo::select('course_category_id')->where('course_id', $courseId)->pluck('course_category_id')->toArray();
        $courseCageoriesInfos=CourseCategory::whereIn('id',$categoriesInfos)->get(); 
        // return 'Text';
        */
         return response()->json($courseCageoriesInfos); 
    }

    public function Session()
    {

        $batches = Batch::where('status', '!=', 0)->get();
       //$sessions = Session::where('status', '!=', 0)->get();
       $sessions = Session::leftJoin('batches', 'sessions.batch_id', '=', 'batches.id')
                            ->select('sessions.*', 'batches.batch_name')
                            ->where('sessions.status', '!=', 0)->get();

        return view('userManagement.Session', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('sessions','batches'));
    }

    public function addsession(Request $request)
    {
        $Batch = $request->input('batch_id');
        $Name = $request->input('session_name');
        $sessionCount = Session::where('batch_id',$Batch)->where('session_name',$Name)->count();
        if($sessionCount>0){                        
            return redirect()->route('Session')->with('error', 'Session Already Exist');
        } else {

            $courseId = Batch::where('id', $Batch)->where('status', 1)->first()->batch_course;
            $courseCageoriesInfos = CourseBasicInfo::where('id',$courseId)->first()->duration;    

            if($courseCageoriesInfos<$request->input('session_value')) {
                return redirect()->route('Session')->with('error', 'Session Duration Exceed with Course Duration. Please check and update');
            }
        // Create a new UserGroup instance
        $session = new Session([
            'batch_id' => $request->input('batch_id'),
            'session_name' => $request->input('session_name'),
            'session_value' => $request->input('session_value'),
            'created_by' => auth()->user()->id,
            'status' => 1,
        ]);
        // Save the UserGroup instance to the database
        $session->save();

        //$sessions = Session::where('status', true)->get();
        
       $sessions = Session::leftJoin('batches', 'sessions.batch_id', '=', 'batches.id')
       ->select('sessions.*', 'batches.batch_name')
       ->where('sessions.status', '!=', 0)->get();
        $batches = Batch::where('status', '!=', 0)->get();
        return view('userManagement.Session', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('sessions'));
        }
    }

    public function editSession($id)
    {
        // Create a new UserGroup instance
        $session = Session::find($id);
        $editModel = true;
        $basicInfos = Batch::where('status', '!=', 0)->get();
        return view('userManagement.Session', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('session', 'basicInfos','editModel'));
    }

    public function updatesession(Request $request)
    {
        $Batch = $request->input('batch_name');
        $Name = $request->input('session_name');
        $courseId = Batch::where('id', $Batch)->where('status', 1)->first()->batch_course;
        $courseCageoriesInfos = CourseBasicInfo::where('id',$courseId)->first()->duration;    

        if($courseCageoriesInfos<$request->input('session_value')) {
            return redirect()->route('Session')->with('error', 'Session Duration Exceed with Course Duration. Please check and update');
        }
        $session = Session::find($request->id);
        if ($session) {
            $session->update([
                'session_name' => $request->session_name,
                'session_value' => $request->session_value,
            ]);
        }
        return redirect()->route('Session')->with('success', 'Session Updated Successfully');
    }

    public function deleteSession($id)
    {
        $session = Session::find($id);
        if ($session) {
            $session->status = 0;
            $session->save();
        }
        return redirect()->route('Session')->with('success', 'User Group Deleted Successfully');
    }


    // User Account 
    public function createuseraccount()
    {
        $usergroups = UserGroup::all();

        return view('userManagement.createuseraccount', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('usergroups'));
    }

    public function useraccountstore(Request $request)
    {

        $validateData = Validator::make($request->all(), [
            'user_group_id' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
            'name' => '',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'role' => '',
            'phone_number' => '',
            'status' => 'boolean',
        ]);

        if ($validateData->fails()) {
            return redirect()->route('viewuseraccount')->with('error', $validateData->errors()->all());
        }

        $request->validate([
            'user_group_id' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
            'name' => '',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'role' => '',
            'phone_number' => '',
            'status' => 'boolean',
        ]);

        $groupId = $request->user_group_id;
        // Create a new UserGroup instance
        $user = User::create([
            'user_group_id' => $groupId,
            'password' => $request->input('password'),
            'name' => $request->input('last_name') . ' ' . $request->input('first_name'),
            'email' => $request->input('email'),
            'phone_code' => $request->input('phone_code'),
            'phone_number' => $request->input('phone_number'),
        ]);

        UserAccountBasicInfo::create([
            'user_id' => $user->id,
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
        ]);

        $Getgorup = UserGroupPermission::where('user_group_id', $groupId)->first();

        // Create a new UserGroupPermission instance
        $UserAccountPermission = new UserAccountPermission([
            'user_group_id' => $groupId,
            'user_id' => $user->id,
            'create_user' => $Getgorup->create_user,
            'edit_user' => $Getgorup->edit_user,
            'view_user' => $Getgorup->view_user,
            'delete_user' => $Getgorup->delete_user,
        ]);
        // Save the UserAccountPermission instance to the database
        $UserAccountPermission->save();


        try {
            
                $userData = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $request->input('password'),
                ];
                Mail::to($user->email)->send(new WelcomeEmail($userData));
                
        } catch (\Exception $e) {

           return redirect()->route('viewuseraccount')->with('error', 'User Account Created Successfully. Issue on Email Server.');

        }


        return redirect()->route('viewuseraccount')->with('success', 'User Account Created Successfully');

    }
    public function viewuseraccount()
    {

        $useraccounts = User::with(['userGroup', 'userAccountBasicInfo'])->where('id','!=',1)->get();

        /*$useraccounts = User::join('usergroup', 'useraccount.usergroup', '=', 'usergroup.id')
            ->join('user_account_basic_info', 'User.id', '=', 'user_account_basic_info.user_id')
            ->select('useraccount.*', 'usergroup.name as usergroup_name', 'usergroup.role as usergroup_role', 'usergroup.username as usergroup_username', 'usergroup.organization as usergroup_organization', 'user_account_basic_info.verficationstatus')
            ->get();*/


        $usergroups = UserGroup::all();
        $country = Country::all();
        $Model = false;
        $permissionModel = false;
        return view('userManagement.viewuseraccount', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('useraccounts', 'usergroups', 'Model', 'permissionModel', 'country'));
    }
    public function viewaccountpermission($id)
    {
        $useraccounts = User::join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
            ->select('users.*', 'user_groups.name as usergroup_name', 'user_groups.role as usergroup_role', 'user_groups.username as usergroup_username', 'user_groups.organization as usergroup_organization')
            ->get();
        $usergroups = UserGroup::all();
        $user = User::find($id);
        $useraccountpermissions = UserAccountPermission::where('user_id', $id)->first();
        if (!isset ($useraccountpermissions)) {
            $create_user = 0;
            $edit_user = 0;
            $view_user = 0;
            $delete_user = 0;
            $userGroupPermission = UserGroupPermission::where('user_group_id', $user->user_group_id)->first();
            if (isset ($userGroupPermission)) {
                $create_user = $userGroupPermission->create_user;
                $edit_user = $userGroupPermission->edit_user;
                $view_user = $userGroupPermission->view_user;
                $delete_user = $userGroupPermission->delete_user;
            }
            $useraccountpermissions = UserAccountPermission::create([
                'user_id' => $id,
                'user_group_id' => $user->user_group_id,
                'create_user' => $create_user,
                'edit_user' => $edit_user,
                'view_user' => $view_user,
                'delete_user' => $delete_user,

            ]);
        }
        $Model = false;
        $permissionModel = true;
        return view('userManagement.viewuseraccount', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('useraccountpermissions', 'useraccounts', 'usergroups', 'Model', 'permissionModel'));
    }


    public function editaccountpermission(Request $request)
    {
        // Create a new UserGroup instance
        $userAccount = UserAccountPermission::find($request->id);
        if ($userAccount) {
            $userAccount->update([
                'create_user' => $request->create_user,
                'edit_user' => $request->edit_user,
                'view_user' => $request->view_user,
                'delete_user' => $request->delete_user,
            ]);
        }
        return redirect()->route('viewuseraccount')->with('success', 'User Account Permission Updated Successfully');
    }


    public function editUserAccount($id)
    {

        $useraccounts = User::with(['userGroup'])->get();
        $country = Country::all();


        /*$useraccounts = User::join('usergroup', 'useraccount.usergroup', '=', 'usergroup.id')
            ->select('useraccount.*', 'usergroup.name as usergroup_name', 'usergroup.role as usergroup_role', 'usergroup.username as usergroup_username', 'usergroup.organization as usergroup_organization')
            ->get();*/
        $edituseraccount = User::with(['userAccountBasicInfo'])->where('id', $id)->first();
        $usergroups = UserGroup::all();
        $Model = true;
        return view('userManagement.viewuseraccount', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('useraccounts', 'usergroups', 'edituseraccount', 'Model','country'));
    }
    public function updateuseraccount(Request $request)
    {
    
        $validateData = $request->validate([
            'id' => 'required',
            'user_group_id' => 'required|string',
            'password' => 'confirmed',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string',
        ]);

        //print_r($validateData); exit;
        /*
        if ($validateData->fails()) {
            return redirect()->route('viewuseraccount')->with('error', $validateData->errors()->all());
        }
        */
        //print_r($validateData); exit;


        $user = User::find($request->id);
        if ($user) {
            if (isset ($request->password) && !empty ($request->password)) {
                $user->update([
                    'password' => $request->password,
                ]);
            }
            $user->update([
                'user_group_id' => $request->user_group_id,
                'name' => $request->last_name . ' ' . $request->first_name,
                'email' => $request->email,
                'phone_code' => $request->phone_code,
                'phone_number' => $request->phone_number,
            ]);

            $user_account_info = UserAccountBasicInfo::where('user_id', $request->id)->first();
            if ($user_account_info) {
                $user_account_info->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ]);
            } else {
                UserAccountBasicInfo::create([
                    'user_id' => $request->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ]);
            }
        }
        return redirect()->route('viewuseraccount')->with('success', 'User Account Updated Successfully');
    }

    public function deleteuseraccount(Request $request)
    {
        try {
            $userGroup = User::find($request->id);
            if ($userGroup) {
                $checkMap = MapCourse::where('instructor_id', $userGroup->id)->count();
                if($checkMap>0){
                    return redirect()->route('viewuseraccount')->with('error', 'User Already Connected with Course. Not able to Delete');
                    }

                UserAccountPermission::where('user_id', $userGroup->id)->delete();
                UserAccountBasicInfo::where('user_id', $userGroup->id)->delete();
                $userGroup->delete();
            }
        } catch (\Exception $e) {
        
            return redirect()->route('viewuseraccount')->with('error', 'User Already Connected with Course. Not able to Delete');
    
        }
        
        return redirect()->route('viewuseraccount')->with('success', 'User Group Deleted Successfully');
    }

    public function additionalinfo($id)
    {

        $useraccounts = User::with(['userGroup', 'userAccountBasicInfo'])->get();
        /*$useraccounts = User::join('usergroup', 'useraccount.usergroup', '=', 'usergroup.id')
            ->join('user_account_basic_info', 'User.id', '=', 'user_account_basic_info.user_id')
            ->select('useraccount.*', 'usergroup.name as usergroup_name', 'usergroup.role as usergroup_role', 'usergroup.username as usergroup_username', 'usergroup.organization as usergroup_organization', 'user_account_basic_info.verficationstatus')
            ->get();*/
        $profiles = User::with(['userAccountBasicInfo'])->where('id', $id)->first();
        $Countrys = Country::all();        
        $country = $Countrys;
        $states = [];
        if (isset ($profiles->userAccountBasicInfo->country_id)) {
            $states = State::where('country_id', $profiles->userAccountBasicInfo->country_id)->get();
        }
        $usergroups = UserGroup::all();
        $additionalinfoModel = true;

        return view('userManagement.viewuseraccount', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('usergroups', 'useraccounts', 'profiles', 'Countrys', 'country', 'states', 'additionalinfoModel'));
    }
    public function additionalinfoUpdate(Request $request)
    {
        $photonamepath = null;
        // Handle the file upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoname = time() . '.' . $photo->extension();
            $photo->move(public_path('uploads'), $photoname);
            $photonamepath = $photoname;
        }

        $phone_number = $request->input('phone_number');
        $address = $request->input('address');
        $state = $request->input('state');
        $countryGet = Country::where('id', $request->input('country'))->get();

        $country = $countryGet[0]->name;
        $city = $request->input('city');
        $pindcode = $request->input('pincode');
        $lanuageskills = $request->input('languageskills');
        $lanuageskillratio = $request->input('languageskillratio');
        $typeoflearning = $request->input('typeoflearning');
        $idproof = $request->input('idproof');
        $idproofnumber = $request->input('idproofnumber');
        $plan = $request->input('plan');
        
        $user = User::find($request->id);

        if ($user) {
            if(isset($phone_number)){
                $user->phone_number = $phone_number;
                $user->save();
            }
            $user_account_basic_info = UserAccountBasicInfo::where('user_id',$request->id)->first();
            if(!isset($user_account_basic_info)){
                $user_account_basic_info = UserAccountBasicInfo::create([
                    'user_id'=> $request->id,
                ]);
            }

            $user_account_basic_info->update([
                'address' => $address,
                'state' => $state,
                'country' => $country,
                'city' => $city,
                'pincode' => $pindcode,
                'languageskills' => $lanuageskills,
                'languageskillratio' => $lanuageskillratio,
                'photo' => $photonamepath,
                'typeoflearning' => $typeoflearning,
                'idproof' => $idproof,
                'idproofnumber' => $idproofnumber,
                'plan' => $plan,
            ]);

            return redirect()->route('viewuseraccount')->with('success', 'Profile Update Successfully');
        }
        return redirect()->route('viewuseraccount')->with('error', 'Invalid User');
    }
}
