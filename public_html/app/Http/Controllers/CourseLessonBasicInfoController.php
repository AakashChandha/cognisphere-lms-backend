<?php

namespace App\Http\Controllers;

use App\Models\CourseBasicInfo;
use App\Models\CourseCategory;
use Illuminate\Http\Request;

use App\Models\CourseLessonBasicInfo;
use Illuminate\Support\Facades\Auth;

use App\Models\Batch;
use App\Models\Session;

class CourseLessonBasicInfoController extends Controller
{
    // Retrieve all records
    public function index()
    {
        $courseLessonBasicInfos = CourseLessonBasicInfo::with('CourseBasicInfo')->get();
        $basicInfos = CourseBasicInfo::all();
        $categories = CourseCategory::all();
        return view('course.lesson-basic-info', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courseLessonBasicInfos','basicInfos','categories'));
    }

    // Show the form for creating a new record
    public function create()
    {
        return view('course_lesson_basic_info.create');
    }

    // Store a newly created record in the database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'lesson_id' => 'required',
            'course_name' => 'required'
        ],[
            'name.required' => 'The name field is required',
            'lesson_id.required' => 'The name field is required',
            'course_name.required' => 'The name field is required',
            // 'course_name.integer' => 'The name field must be an integer',
            'duration.required' => 'The name field is required',
        ]);

        $Category = $request->input('course_category_id');
        $Course = $request->input('course_name');
        $LName = $request->input('name');
        $LID = $request->input('lesson_id');

        $UnitCount = CourseLessonBasicInfo::where('course_category_id',$Category)
                    ->where('course_basic_info_id',$Course)
                    ->where('lesson_name',$LName)
                    ->where('lesson_id',$LID)
                    ->count();

        if($UnitCount>0){
            return redirect()->route('course-lesson-basic-info')->with('error', 'Unit Already Exist.');
        } else {

        $CourseDurationInfo = CourseBasicInfo::where('course_category_id',$Category)
                ->where('id',$Course)
                ->first();

        $CourseDuration = $CourseDurationInfo->duration;        

        $UnitDuration = CourseLessonBasicInfo::where('course_category_id',$Category)
        ->where('course_basic_info_id',$Course)
        ->sum('duration');    
        $UnitDurationwithNew = $UnitDuration+$request->input('duration'); 


        if($CourseDuration < $UnitDurationwithNew){
            return redirect()->route('course-lesson-basic-info')->with('error', 'Unit Hour Exceed Limit.');
        }
         
        $userId = Auth::user()->id;
        $category = new CourseLessonBasicInfo();
        $category->lesson_name = $request->input('name');
        $category->lesson_id = $request->input('lesson_id');
        $category->duration = $request->input('duration');
        $category->course_category_id = $request->input('course_category_id');
        $category->course_basic_info_id = $request->input('course_name'); 
        $category->created_by = $userId; 
        $category->save(); 
        
        return redirect()->route('course-lesson-basic-info')->with('success', 'Record created successfully.');
        }
    }

    // Show the form for editing the specified record
    public function edit($id)
    {
        $courseLessonBasicInfos = CourseLessonBasicInfo::with('CourseBasicInfo')->get();
        $editLessonInfo = CourseLessonBasicInfo::findOrFail($id);
        $basicInfos = CourseBasicInfo::all();
        //$courseCageoriesInfos=CourseCategory::where('id',$editLessonInfo->course_category_id)->get(); 
        $courseCageoriesInfos=CourseCategory::all();
        $basicInfos = CourseBasicInfo::where('course_category_id', $editLessonInfo->course_category_id)->get();
        $Model = true;
        $categories = CourseCategory::all();
        return view('course.lesson-basic-info',['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact("basicInfos","courseLessonBasicInfos","editLessonInfo","Model","courseCageoriesInfos","categories")); 
    }

    // Update the specified record in the database
    public function update(Request $request)
    {
        
         $request->validate([
            'name' => 'required',
            'lesson_id' => 'required',
            'course_name' => 'required',
            'course_category_id' => 'required',
            'duration' => 'required'
            ]);

            
        $id = $request->category_id;    
        $Category = $request->course_category_id;
        $Course = $request->course_name;
        $LName = $request->name;
        $LID = $request->lesson_id;

        $UnitCount = CourseLessonBasicInfo::where('course_category_id',$Category)
                    ->where('course_basic_info_id',$Course)
                    ->where('lesson_name',$LName)
                    ->where('lesson_id',$LID)
                    ->where('id','!=',$id)
                    ->count();

        if($UnitCount>0){

            return redirect()->route('course-lesson-basic-info')->with('error', 'Unit Name Already Exist with Another Name.');

        } else {

            
        $CourseDurationInfo = CourseBasicInfo::where('course_category_id',$Category)
        ->where('id',$Course)
        ->first();

$CourseDuration = $CourseDurationInfo->duration;        

$UnitDuration = CourseLessonBasicInfo::where('course_category_id',$Category)
->where('course_basic_info_id',$Course)
->where('id','!=',$id)
->sum('duration');    
$UnitDurationwithNew = $UnitDuration+$request->input('duration'); 


if($CourseDuration < $UnitDurationwithNew){
    return redirect()->route('course-lesson-basic-info')->with('error', 'Unit Hour Exceed Limit.');
}

            $id = $request->category_id;     
            $userId = Auth::user()->id;
            $category = CourseLessonBasicInfo::findOrFail($id);
            $category->lesson_name = $request->input('name');
            $category->lesson_id = $request->input('lesson_id');
            $category->course_category_id = $request->input('course_category_id');
            $category->duration = $request->input('duration');
            $category->course_basic_info_id = $request->input('course_name'); 
            $category->created_by = $userId;
            $category->save();  

        return redirect()->route('course-lesson-basic-info')->with('success', 'Record updated successfully.');

        }
    }

    // Remove the specified record from the database
    public function destroy($id)
    {
      
        $category = CourseLessonBasicInfo::findOrFail($id);
        $category->status = false;
        $category->save(); 
        return redirect()->route('course-lesson-basic-info')->with('success', 'Record deleted successfully.');
    }
    public function  getCoursesLesson($courseId)
    {
        $courseBasicInfos = courseLessonBasicInfo::where('course_basic_info_id', $courseId)->get(); 
        
        return response()->json($courseBasicInfos); 
    }

    public function  getContentLesson($Id)
    {
        $courseId=CourseBasicInfo::select('course_id')->where('id',$Id)->first()->course_id;
        $categoriesInfos = CourseBasicInfo::select('course_category_id')->where('course_id', $courseId)->pluck('course_category_id')->toArray();
        $courseCageoriesInfos=CourseCategory::whereIn('id',$categoriesInfos)->get(); 
        // return 'Text';
        
         return response()->json($courseCageoriesInfos); 
    }

    
    public function  getCategoryCourses($courseId)
    {
        //$courseBasicInfos = courseLessonBasicInfo::where('course_basic_info_id', $courseId)->get(); 
        $basicInfos = CourseBasicInfo::where('course_category_id', $courseId)->get();
        return response()->json($basicInfos); 
    }

    
    public function  getCoursesBatch($courseId)
    {
        $courseBasicInfos = Batch::where('batch_course', $courseId)->where('status',1)->get(); 
        
        return response()->json($courseBasicInfos); 
    }
    
    public function  getBatchSession($courseId)
    {
        $courseBasicInfos = Session::where('batch_id', $courseId)->where('status',1)->get(); 
        
        return response()->json($courseBasicInfos); 
    }

    
}
    //

