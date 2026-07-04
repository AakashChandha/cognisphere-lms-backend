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
use App\Models\Attendance;
use DB;
use App\Models\Country;
use App\Models\State;
use App\Models\Batch;
use App\Models\Session;


class AttendanceController extends Controller
{
    //

    public function serachentrollment(Request $request)
    {
        
        $session_ID = $request->input('session');
        $session_IDs = Session::where('id', $session_ID)->first();

        $date = $request->input('date');

        $course_id = $request->input('course_id');
        $batch_id = $request->input('batch_id');
        $session = $request->input('session');
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();
        $batchs = Batch::where('status', '!=', 0)->get();
        $sessions = Session::where('status', '!=', 0)->get();


        $getEnrollmentUsers = Entrollment::where('course_id', $course_id)->where('batch_id', $batch_id)->where('course_type', 'offline')->where('enrolled', 1)->get();
            foreach($getEnrollmentUsers as $getEnrollmentUser)
            {
                $getAttendance = Attendance::where('learner_id', $getEnrollmentUser->user_id)->where('course_id', $course_id)->where('date', $date)->first();
                if($getAttendance)
                {
                $UseracccountIDS[] = "";
                }
                else
                {
                    $UseracccountIDS[] = $getEnrollmentUser->user_id;
                }
            }
            $UseracccountIDS[] = "";

        if ($UseracccountIDS != '' && $UseracccountIDS != null) {
            $Entrollments = Entrollment::select(
                'entrollment.id as enrollment_id',
                'course_basic_infos.course_name',
                'batch.batch_name',
                'entrollment.user_id',
                'entrollment.course_type',
                'user_groups.name as usergroup_name',
                'user_account_basic_infos.first_name as useraccount_first_name',
                'user_account_basic_infos.last_name as useraccount_last_name',
                'entrollment.created_by',
                'entrollment.status',
                'entrollment.created_at',
                'entrollment.updated_at'
            )
                ->join('user_groups', 'entrollment.user_group_id', '=', 'user_groups.id')
                ->join('user_account_basic_infos', 'entrollment.user_id', '=', 'user_account_basic_infos.user_id')
                ->join('course_basic_infos', 'entrollment.course_id', '=', 'course_basic_infos.id')
                ->join('batch', 'entrollment.batch_id', '=', 'batch.id')
                ->where('entrollment.enrolled', '=', 1)
                ->where('entrollment.course_id', '=', $course_id)
                ->where('entrollment.batch_id', '=', $batch_id)
                ->whereIn('entrollment.user_id', $UseracccountIDS)
                ->where('entrollment.course_type', '=', 'offline')
                ->get();
        } else {
            $Entrollments = [];
        }

        return view('instructor.attendaceInstructor', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('Entrollments','sessions','batchs', 'course_id', 'batch_id', 'session', 'courses', 'usergroups', 'users', 'date', 'session_IDs'));
    }

    public function markattendance(Request $request)
    {
        $enrollmentIds = $request->input('enrollmentIds');

        $uncheckedEnrollmentIds = $request->input('uncheckedEnrollmentIds');

        $uncheckedEnrollmentIds = explode(',', $uncheckedEnrollmentIds);

        $enrollmentIds = explode(',', $enrollmentIds);


            if(!empty($enrollmentIds[0]))
            {
                foreach ($enrollmentIds as $entrollmentID)
                {
                    $getEntrollDetails = Entrollment::find($entrollmentID);
                    $createAttendance = Attendance::create([
                        'course_id' => $getEntrollDetails->course_id,
                        'learner_id' => $getEntrollDetails->user_id,
                        'session' => $request->session_id,
                        'date' => $request->date,
                        'attendance' => 1,
                        'created_by' => auth()->user()->id,
                        'status' => 1,
                    ]);
                    $getEntrollDetails->attendance_status = 1;
                    $getEntrollDetails->save();
                }
            }
            if(!empty($uncheckedEnrollmentIds[0])) 
            {
                foreach ($uncheckedEnrollmentIds as $uncheckedEnrollmentId)
                {
                    $getEntrollDetails = Entrollment::find($uncheckedEnrollmentId);
                    $createAttendance = Attendance::create([
                        'course_id' => $getEntrollDetails->course_id,
                        'learner_id' => $getEntrollDetails->user_id,
                        'session' => $request->session_id,
                        'date' => $request->date,
                        'attendance' => 0,
                        'created_by' => auth()->user()->id,
                        'status' => 1,
                    ]);
                    $getEntrollDetails->attendance_status = 1;
                    $getEntrollDetails->save();
                }
            }
            return redirect()->route('attendaceInstructor')->with('success', 'Attendance Successfully Created'); 
    }

    public function viewSummary()
    {
        $Attendances = [];
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();
        $batchs = Batch::where('status', '!=', 0)->get();

        return view('instructor.viewSummary', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Attendances', 'courses', 'usergroups', 'users'));
    }
    public function viewsummarymark(Request $request)
    {
        $course_id = $request->input('course_id');
        $batch_id = $request->input('batch_id');
        $session = $request->input('session');
        $courses = CourseBasicInfo::all();
        $usergroups = UserGroup::all();
        $users = User::all();
        $batchs = Batch::where('status', '!=', 0)->get();


        $Attendances = DB::table('attendance as a')
            ->select(
                'a.learner_id',
                DB::raw('MAX(a.course_id) as course_id'),
                DB::raw('MAX(c.course_name) as course_name'),
                DB::raw('MAX(u.first_name) as name'),
                DB::raw('MAX(s.session_name) as session_name'), // Add this line to select the session name
                DB::raw('MAX(a.date) as date'),
                DB::raw('COUNT(a.attendance) AS total_count'),
                DB::raw('SUM(a.attendance) AS total_attendance'),
                DB::raw('ROUND((SUM(a.attendance) / COUNT(a.attendance)) * 100) as total_percentage')
            )
            ->join('course_basic_infos as c', 'a.course_id', '=', 'c.id')
            ->join('users as u', 'a.learner_id', '=', 'users.id')
            ->join('session as s', 'a.session', '=', 's.id') // Join the session table
            ->groupBy('a.learner_id')
            ->where('a.course_id', '=', $course_id)
            ->get();
        return view('instructor.viewSummary', ['title' => 'LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('batchs','Attendances', 'course_id', 'batch_id', 'session', 'courses', 'usergroups', 'users'));
    }
}
