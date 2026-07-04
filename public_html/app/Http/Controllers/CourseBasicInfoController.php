<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\CourseLessonBasicInfo;
use App\Models\CourseBasicInfo;
use App\Models\CourseCategory; // Import the CourseCategory class
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; // Import the Auth class 
use Illuminate\Support\Facades\Log; // Import the Log class
class CourseBasicInfoController extends Controller
{
    public function index()
    {
        $courseBasicInfos = CourseBasicInfo::with('courseCategory','user')->get();
        $categories = CourseCategory::all();
        //  Log::info($courseBasicInfos[0]->course_category);
        // exit;
        return view('course.basic-info-list',['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courseBasicInfos','categories'));
    }
    public function create()
    {
        $courseBasicInfos = CourseBasicInfo::all();
        $categories = CourseCategory::all();
        return view('course.basic-info',['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courseBasicInfos','categories'));
    }
 

    public function store(Request $request)
    { 
            $request->validate([
                'name' => 'required',
                'category' => 'required',
                'course_id' => 'required',
                'duration_type' => 'required',
                'duration' => 'required',
                //'credits' => 'required',
                'course_price' => 'required',
                'course_size'=>'required'

            ]);
            
            if ($request->hasFile('course_image')) {

                $photo = $request->file('course_image');
                $photoname = time() . '.' . $photo->extension();
                $photo->move(public_path('uploads'), $photoname);
                $photonamepath = $photoname;
            }
            else
            {
                $photonamepath = "";
            }

            $Category = $request->input('category');
            $Name = $request->input('name');
            $CourseID = $request->input('course_id');

            $CourseCount = CourseBasicInfo::where('course_category_id',$Category)
            ->where('course_name',$Name)
            ->where('course_id',$CourseID)
            ->count();

            if($CourseCount>0) {

                return redirect()->route('course-basic-info')
                ->with('error', 'Course basic info Already Exist.');
                
            } else {

                $userId = Auth::user()->id;
                $BasicInfo = new CourseBasicInfo();
                $BasicInfo->course_name = $request->input('name');
                $BasicInfo->course_category_id  =  $request->input('category');
                $BasicInfo->course_id  =  $request->input('course_id');
                $BasicInfo->duration_type = $request->input('duration_type');
                $BasicInfo->duration  =  $request->input('duration');
                //$BasicInfo->credits = $request->input('credits');
                $BasicInfo->course_price  =  $request->input('course_price');
                $BasicInfo->course_size  =  $request->input('course_size');
                $BasicInfo->course_image  =  $photonamepath;
                $BasicInfo->course_description = $request->input('course_description');
                $BasicInfo->created_by = $userId; 
                $BasicInfo->save(); 
            return redirect()->route('course-basic-info')
                ->with('success', 'Course basic info created successfully.');

            }
    }
 

    public function edit($id)
    {
        $courseBasicInfos = CourseBasicInfo::with('courseCategory','user')->get();
        $categories = CourseCategory::all(); 
        $editCourseBasicInfo = CourseBasicInfo::findOrFail($id);
        $Model = "Edit";
        
        return view('course.basic-info-list',['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courseBasicInfos','categories','editCourseBasicInfo','Model'));
         
    }

    public function update(Request $request, CourseBasicInfo $courseBasicInfo)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'course_id' => 'required',
            'duration_type' => 'required',
            'duration' => 'required',
            'course_description' => 'required',
            'course_price' => 'required',
            'course_size'=>'required'

        ]);  

        $id = $request->category_id; 
        $Category = $request->input('category');
        $Name = $request->input('name');
        $CourseID = $request->input('course_id');

        $CourseCount = CourseBasicInfo::where('course_category_id',$Category)
        ->where('course_name',$Name)
        ->where('course_id',$CourseID)
        ->where('id','!=',$id)
        ->count();

        if($CourseCount>0) {

            return redirect()->route('view-course-basic-info')
            ->with('error', 'Course basic info Already Exist.');
            
        } else {

        $id = $request->category_id; 

        $BasicInfo = CourseBasicInfo::findOrFail($id);
        $BasicInfo->course_name = $request->input('name');
        $BasicInfo->course_category_id  =  $request->input('category');
        $BasicInfo->course_id  =  $request->input('course_id');
        $BasicInfo->duration_type = $request->input('duration_type');
        $BasicInfo->duration  =  $request->input('duration');
        //$BasicInfo->credits = $request->input('credits');
        // $BasicInfo->course_image  =  ;
        $BasicInfo->course_description = $request->input('course_description');
        $BasicInfo->course_price  =  $request->input('course_price');
        $BasicInfo->course_size  =  $request->input('course_size');  
        $BasicInfo->save(); 

        return redirect()->route('view-course-basic-info')->with('success', 'Course basic info updated successfully.');
        }
    }

    public function destroy($id)
    { 
        //delete all contents belongs to the course
        Content::where('course_id',$id)->delete();
        $lessons_belongs_to_the_course = CourseLessonBasicInfo::where('course_basic_info_id',$id)->pluck('id')->toArray();
        Content::whereIn('course_lesson_id',$lessons_belongs_to_the_course)->delete();
        
        //delete all cource lessons belongs to the course
        CourseLessonBasicInfo::where('course_basic_info_id',$id)->delete();
        $info = CourseBasicInfo::findOrFail($id);
        $info->delete();
        return redirect()->route('view-course-basic-info')
            ->with('success', 'Course basic info deleted successfully.');
    }

    public function ChangeStatusCourse(Request $request)
    {
        $courseBasicInfo = CourseBasicInfo::where('id', $request->course_id)->first();
        $courseBasicInfo->status = $request->status;
        $courseBasicInfo->save();
        return response()->json(['success' => 'Status change successfully.']);
    }
}
     

