<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\CourseBasicInfo;
use App\Models\CourseLessonBasicInfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Uid\Uuid;
use Illuminate\Support\Facades\DB;
use App\Models\CourseCompleted;
use App\Models\MapCourse;
use App\Models\User;
use App\Models\Entrollment;
use App\Models\QuizDetails;
use App\Models\QuizQuestion;
use App\Models\CourseCompletedTracking;
use App\Models\VideoContent;
use App\Models\ResoureseModel;
use App\Models\CourseCategory;

use App\Models\AudioContent;

class ContentController extends Controller
{
    // Show all contents
    public function index()
    {
        $contents = Content::with('courses', 'courseLessonInfo')->get();
        $courses = CourseBasicInfo::all();
        return view('contents.content-list', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('contents', 'courses'));
    }

    // Show the form for creating a new content
    public function create()
    {
        $courses = CourseBasicInfo::all();
        $categories = CourseCategory::all();
        return view('contents.add-content', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses', 'categories'));
    }

    // Store a newly created content in the database
    public function store(Request $request)
    {
        $request->validate([
            'courseName' => 'required',
            'courseLesson' => 'required',
            'name' => 'required',
            'contentType' => 'required',
        ]);

        $Category = $request->input('courseCategory');
        $Course = $request->input('courseName');
        $Lesson = $request->input('courseLesson');
        $Content = $request->input('name');

        $ContentCount = Content::where('course_category_id', $Category)
            ->where('course_id', $Course)
            ->where('course_lesson_id', $Lesson)
            ->where('content_name', $Content)
            ->count();

        if ($ContentCount > 0) {

            return redirect()->route('content')->with('error', 'Content Already Exist.');
        } else {

            $userId = Auth::user()->id;
            $courseId = $request->input('courseName');
            $courseCategoryId = $request->input('courseCategory');
            $courseLesson = $request->input('courseLesson');
            $contentType = $request->input('contentType');
            $countentValue = $request->input('contentValue');
            $countentFilePath = "";
            if ($contentType ==  2 || $contentType ==  3) {
                if ($request->file('contentFile')) {
                    $countentFilePath = $request->file('contentFile')->store('contents', 'public');
                    $countentValue = $request->file('contentFile')->getClientOriginalName();
                } else {
                    $countentFilePath = '';
                }
            }

            $contentorder = Content::where('course_id', $courseId)->where('course_lesson_id', $courseLesson)->count();
            $Content = new Content();
            $Content->content_name = $request->input('name');
            $Content->course_id  = $courseId;
            $Content->course_category_id  = $courseCategoryId;
            $Content->course_lesson_id  = $courseLesson;
            $Content->content_id = Uuid::v4();
            if ($countentFilePath != '') {
                $Content->file_path = $countentFilePath;
            }
            $Content->content_type  = $contentType;
            $Content->content_value = $countentValue;
            $Content->content_order = $contentorder + 1;
            $Content->created_by = $userId;
            // Set other properties as needed
            $Content->save();


            return redirect()->route('content')->with('success', 'Content created successfully');
        }
    }

    // Show the form for editing the specified content
    public function edit($id)
    {

        $editContent = Content::where('id', $id)->get();

        $course_id = $editContent[0]->courses->id;
        $courses = CourseBasicInfo::all();
        $courseLessons = CourseLessonBasicInfo::where('course_basic_info_id', $course_id)->get();
        $contents = Content::with('courses', 'courseLessonInfo')->get();
        $Model = true;
        return view('contents.content-list', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('contents', 'editContent', 'courses', 'courseLessons', "Model"));
    }

    // Update the specified content in the database
    public function update(Request $request)
    {
        $request->validate([
            'courseName' => 'required',
            'courseLesson' => 'required',
            'name' => 'required',
            'contentType' => 'required',
        ]);

        $id = $request->content_id;
        $Category = $request->input('courseCategory');
        $Course = $request->input('courseName');
        $Lesson = $request->input('courseLesson');
        $Content = $request->input('name');

        $ContentCount = Content::where('course_category_id', $Category)
            ->where('course_id', $Course)
            ->where('course_lesson_id', $Lesson)
            ->where('content_name', $Content)
            ->where('id', '!=', $id)
            ->count();

        if ($ContentCount > 0) {

            return redirect()->route('view-content')->with('error', 'Content Already Exist.');
        } else {

            $userId = Auth::user()->id;
            $courseId = $request->input('courseName');
            $courseLesson = $request->input('courseLesson');
            $contentType = $request->input('contentType');
            $countentValue = $request->input('contentValue');
            $countentFilePath = $request->input('file_path');
            if ($contentType ==  2) {

                if ($request->file('contentFile')) {
                    if (Storage::disk('public')->exists($countentFilePath)) {
                        Storage::disk('public')->delete($countentFilePath);
                        echo 'File deleted successfully.';
                    }
                    $countentFilePath = $request->file('contentFile')->store('contents', 'public');

                    $countentValue = $request->file('contentFile')->getClientOriginalName();
                } else {
                    $countentFilePath = '';
                    $countentValue = '';
                }
            }

            $id = $request->content_id;
            $Content = Content::findOrFail($id);
            $Content->content_name = $request->input('name');
            $Content->course_id  = $courseId;
            $Content->course_lesson_id  = $courseLesson;
            $Content->content_id = Uuid::v4();
            if ($request->file('contentFile')) {
                $Content->file_path = $countentFilePath;
                $Content->content_type  = $contentType;
                $Content->content_value = $countentValue;
            }
            $Content->created_by = $userId;
            // Set other properties as needed
            $Content->save();


            return redirect()->route('view-content')->with('success', 'Content edited successfully');
        }
    }

    // Remove the specified content from the database
    public function destroy(Request $request)
    {
        $content = Content::findOrFail($request->id);
        $content->status = false;
        $content->save();


        $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 1, 'complete' => 1, 'status' => 1]);


        return redirect()->route('view-content')->with('success', 'Content deleted successfully');
    }

    public function viewCourseContent(Request $request, $id)
    {

        $course_filter_type = "my_courses";
        if (isset($request->course_filter_type)) {
            $course_filter_type = $request->course_filter_type;
        }

        $useremail = Auth::user()->email;
        $UserAccountId = User::where('email', $useremail)->first();
        $mycoursesIds = Entrollment::where('user_id', $UserAccountId->id)->pluck('course_id')->toArray();

        $courseListMy = [];
        if ($course_filter_type == "allcourse") {
            $courseListMy = CourseBasicInfo::get();
        } else {
            $courses = CourseBasicInfo::whereIn('id', $mycoursesIds)->get();
            foreach ($courses as $course) {
                $course = $course->calculateCompletionPercentage(Auth::user()->id);
                if ($course_filter_type == "my_courses") {
                    $courseListMy[] = $course;
                } else {
                    if ($course_filter_type == "current") {
                        if ($course->completed_percentage < 100) {
                            $courseListMy[] = $course;
                        }
                    } else if ($course_filter_type == "completed") {
                        if ($course->completed_percentage >= 100) {
                            $courseListMy[] = $course;
                        }
                    }
                }
            }
        }

        $course = CourseBasicInfo::find($id);

        $instustcorMaping = MapCourse::where('course_id', $id)->first();

        $instudtcorUser = $instustcorMaping->instructor_id;

        $instustcorMapinguser = User::find($instustcorMaping->instructor_id);

        $CheckCourseCompleted = CourseCompleted::where('course_id', $id)->where('user_id', Auth::user()->id)->count();

        if ($CheckCourseCompleted == 0) {
            $ActiveUser = Auth::user()->id;
            $CountcourseContents = Content::where('course_id', $id)->count();
            $courseContents = Content::where('course_id', $id)->get();

            if ($courseContents->isNotEmpty()) {
                $CourseIDGet = $courseContents[0]->course_id;

                $createCourseCompleted = new CourseCompleted();
                $createCourseCompleted->course_id = $CourseIDGet;
                $createCourseCompleted->user_id = $ActiveUser;
                $createCourseCompleted->Lesson_id = $courseContents[0]->course_lesson_id;
                $createCourseCompleted->count_of_topics = $CountcourseContents;
                $createCourseCompleted->count_of_topics_completed = 0;
                $createCourseCompleted->coureses_topic_percentage = 100;
                $createCourseCompleted->coureses_topic_completed_percentage = 0;
                $createCourseCompleted->status = 1;
                $createCourseCompleted->created_by = $ActiveUser;
                $createCourseCompleted->save();
            }

            $quizlessionDeatils = QuizDetails::all();
        } else {
            /*$courseContents = Content::where('contents.course_id', $id)
                ->leftJoin('quiz_details', 'contents.course_lesson_id', '=', 'quiz_details.lesson_id')
                ->get();*/
            $courseContents = Content::with(['courseLessonInfo.quizDetails.quizQuestions'])->where('contents.course_id', $id)->get();
            $quizlessionDeatils = QuizDetails::all();
        }

        $checkalready = CourseCompletedTracking::where('course_id', $id)->where('user_id', Auth::user()->id)->count();
        $lessioncontentAttened = CourseCompletedTracking::where('course_id', $id)
            ->where('user_id', Auth::user()->id)
            ->orderBy('lesson_id', 'asc')
            ->get();

        foreach ($courseContents as $courseContent) {
            $existingTracking = CourseCompletedTracking::where('course_id', $courseContent->course_id)
                ->where('user_id', Auth::user()->id)
                ->where('lesson_id', $courseContent->course_lesson_id)
                ->where('content_id', $courseContent->id)
                ->orderBy('lesson_id', 'asc')
                ->first();

            if (!$existingTracking) {
                $createCourseCompletedTracking = new CourseCompletedTracking();
                $createCourseCompletedTracking->course_id = $courseContent->course_id;
                $createCourseCompletedTracking->user_id = Auth::user()->id;
                $createCourseCompletedTracking->lesson_id = $courseContent->course_lesson_id;
                $createCourseCompletedTracking->content_id = $courseContent->id;
                $createCourseCompletedTracking->progress = 0;
                $createCourseCompletedTracking->status = 0;
                $createCourseCompletedTracking->save();
            }
        }

        $CourseCompletedDetails = CourseCompleted::where('course_id', $id)->where('user_id', Auth::user()->id)->first();

        $content_id = '';

        $QuizquestionID = QuizDetails::where('course_id', $id)->first();
        if ($QuizquestionID) {
            /*
            $questionidsa =  $QuizquestionID->question_id;
            //$questionidsa =  trim($QuizquestionID->question_id, "quiz"); ;
            $trimquestionidsa = trim($questionidsa, "quiz");
            $trimquestionidsa =  $trimquestionidsa+1;
            echo $trimquestionidsa;
            echo 'checking';    
            */
            $questionidsa =  $QuizquestionID->question_id;
            $trimquestionidsa =  $QuizquestionID->id;
        } else {
            $trimquestionidsa =  0;
            $questionidsa = 0;
        }

        $quizDetails = QuizDetails::join('course_basic_infos', 'quiz_details.course_id', '=', 'course_basic_infos.id')
            ->join('course_lesson_basic_infos', 'quiz_details.lesson_id', '=', 'course_lesson_basic_infos.id')
            ->where('quiz_details.question_id', $questionidsa)
            ->select('quiz_details.*', 'course_basic_infos.course_name as course_name', 'course_lesson_basic_infos.lesson_name as lesson_name')
            ->first();

        $quizQuestions = QuizQuestion::where('quiz_id', $trimquestionidsa)->get();

        $coursecompletedtracking = CourseCompletedTracking::where('course_id', $id)
            ->where('user_id', Auth::user()->id)
            ->orderBy('lesson_id', 'asc')
            ->get();

        $courseTrackingLastiDs = CourseCompletedTracking::where('course_id', $id)
            ->where('user_id', Auth::user()->id)->where('complete', 0)
            ->orderBy('lesson_id', 'asc')
            ->first();

        $Totalcount = CourseCompletedTracking::where('course_id', $id)->where('user_id', Auth::user()->id)->count();
        $CompletedCount = CourseCompletedTracking::where('course_id', $id)->where('user_id', Auth::user()->id)->where('progress', 1)->count();

        $percentage = 0;
        if ($Totalcount > 0) {
            $percentage = ($CompletedCount / $Totalcount) * 100;
        }

        $lastseesioncontentid = 0;
        if ($courseTrackingLastiDs == null) {
            $courseTrackingLastiDs = CourseCompletedTracking::where('course_id', $id)
                ->where('user_id', Auth::user()->id)
                ->where('complete', 0)
                ->orderBy('lesson_id', 'asc')
                ->first();
            //print_r($courseTrackingLastiDs);
            if (isset($courseTrackingLastiDs)) {
                $lastseesioncontentid =  $courseTrackingLastiDs->content_id;
            }
        } else {
            $lastseesioncontentid =  $courseTrackingLastiDs->content_id;
        }

        $CourseId = $id;

        $videoContents = VideoContent::where('course_id', $id)->where('status', 1)->get();

        $audioContents = AudioContent::where('course_id', $id)->where('status', 1)->get();

        $resoureseContents = ResoureseModel::where('course_id', $id)->where('status', 1)->get();

        $groupedFiles = [];

        foreach ($resoureseContents as $resoureseContent) {
            $folder = $resoureseContent->folder;
            $groupedFiles[$folder][] = $resoureseContent;
        }


        //$courseLessonBasicInfos = CourseLessonBasicInfo::with(['content'])->where('course_basic_info_id',$id)->where('course_category_id',$course->course_category_id)->where('content.status',1)->get();
        //return $courseContents;

        $courseLessonBasicInfos = CourseLessonBasicInfo::with(['content' => function ($query) {
            $query->where('status', 1);
        }])
            ->where('course_basic_info_id', $id)
            ->where('course_category_id', $course->course_category_id)
            ->get();

        return view('contents.view-course-content', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('quizlessionDeatils', 'CourseId', 'percentage', 'lastseesioncontentid', 'lessioncontentAttened', 'courseListMy', 'instustcorMapinguser', 'content_id', 'courseContents', 'CourseCompletedDetails', 'quizDetails', 'quizQuestions', 'coursecompletedtracking', 'videoContents', 'audioContents', 'resoureseContents', 'course', 'courseLessonBasicInfos', 'groupedFiles'));
    }


    public function learnercoursecontentshow(Request $request)
    {

        $courseContents = Content::find($request->content_id);

        if ($courseContents->content_type == 1) {
            $CourseCompletedTracking = CourseCompletedTracking::where('content_id', $request->content_id)->where('user_id', Auth::user()->id)->first();
            $CourseCompletedTracking->progress = 1;
            $CourseCompletedTracking->status = 1;
            $CourseCompletedTracking->save();

            // $CourseComplation = CourseCompleted::where('course_id',$courseContents->course_id)->where('user_id',Auth::user()->id)->first();

            // $CourseComplation->count_of_topics_completed = $CourseComplation->count_of_topics_completed + 1;
            // $CourseComplation->coureses_topic_completed_percentage = ($CourseComplation->count_of_topics_completed / $CourseComplation->count_of_topics) * 100;
            // $CourseComplation->save();
            return $courseContents->content_value = "<div>" . $courseContents->content_value . "</div>";
        } elseif ($courseContents->content_type == 2) {
            $CourseCompletedTracking = CourseCompletedTracking::where('content_id', $request->content_id)->where('user_id', Auth::user()->id)->first();
            $CourseCompletedTracking->progress = 1;
            $CourseCompletedTracking->status = 1;
            $CourseCompletedTracking->save();
            // $CourseComplation = CourseCompleted::where('course_id',$courseContents->course_id)->where('user_id',Auth::user()->id)->first();

            // $CourseComplation->count_of_topics_completed = $CourseComplation->count_of_topics_completed + 1;
            // $CourseComplation->coureses_topic_completed_percentage = ($CourseComplation->count_of_topics_completed / $CourseComplation->count_of_topics) * 100;
            // $CourseComplation->save();
            $ext = pathinfo($courseContents->file_path, PATHINFO_EXTENSION);
            // echo public_path().'/storage/'.$courseContents->file_path;
            if ($ext == 'pptx' || $ext == 'docx') {
                //$filepath = url('/storage/'.$courseContents->file_path);


                $filepath = url('/storage/app/public/' . $courseContents->file_path);


                return view('courseview.docview', compact('filepath', 'ext'));
                //$filepath = 'https://freetestdata.com/wp-content/uploads/2021/09/Free_Test_Data_100KB_PPTX.pptx';
                return $courseContents->content_value =  '<iframe id="officeFrame" src="https://view.officeapps.live.com/op/view.aspx?src=' . $filepath . '&embedded=true" title="preview my file on nav " frameborder="0" style="width:100%;min-height:640px;"></iframe>';

                //return '<iframe src="https://view.officeapps.live.com/op/view.aspx? src="http://127.0.0.1:8000/storage/contents/gYw3PVPdBHl4nJeXev0q8nDPyZsRsc5LKAaXf0jp.docx" frameborder="0" style="width:100%;min-height:640px;"></iframe>';
                //$filepath = public_path().'/storage/'.$courseContents->file_path;
                //return view('courseview.docview',compact('filepath'));
            } else {

                //$filepath = url('/storage/'.$courseContents->file_path);


                $filepath = url('/storage/app/public/' . $courseContents->file_path);

                return view('courseview.docview', compact('filepath', 'ext'));

                //return $courseContents->content_value = '<object data="' . asset('storage/'.$courseContents->file_path) .'#toolbar=0&navpanes=0&scrollbar=0'.'" width="100%" height="505"></object>';

                //return $courseContents->content_value = '<embed type="application/pdf" src="' . asset('storage/'.$courseContents->file_path) .'#toolbar=0&navpanes=0&scrollbar=0" width="100%" height="505" />';

                //https://docs.google.com/viewer?url=YOUR_FILE_URL&embedded=true

                //return $courseContents->content_value =  '<iframe id="officeFrame" src="https://docs.google.com/viewer?url='.$filepath.'&embedded=true" title="preview my file on nav " frameborder="0" style="width:100%;min-height:640px;"></iframe>';

                return view('courseview.docview', compact('filepath'));
            }
        } elseif ($courseContents->content_type == 3) {
            $CourseCompletedTracking = CourseCompletedTracking::where('content_id', $request->content_id)->where('user_id', Auth::user()->id)->first();
            $CourseCompletedTracking->progress = 1;
            $CourseCompletedTracking->status = 1;
            $CourseCompletedTracking->save();
            // $CourseComplation = CourseCompleted::where('course_id',$courseContents->course_id)->where('user_id',Auth::user()->id)->first();

            // $CourseComplation->count_of_topics_completed = $CourseComplation->count_of_topics_completed + 1;
            // $CourseComplation->coureses_topic_completed_percentage = ($CourseComplation->count_of_topics_completed / $CourseComplation->count_of_topics) * 100;
            // $CourseComplation->save();
            return $courseContents->content_value = '<video id="myVideo" controls autoplay style="width: 100%;"><source src="' . asset('storage/' . $courseContents->file_path) . '" type="video/mp4"><source src="mov_bbb.ogg" type="video/ogg"></video>';
        }
    }

    public function learnercoursecontentquizshow(Request $request)
    {

        $courseContents = Content::find($request->content_id);

        $QuizquestionID = QuizDetails::where('content_id', $request->content_id)->first();
        if ($QuizquestionID) {
            /*
            $questionidsa =  $QuizquestionID->question_id;
            //$questionidsa =  trim($QuizquestionID->question_id, "quiz"); ;
            $trimquestionidsa = trim($questionidsa, "quiz");
            $trimquestionidsa =  $trimquestionidsa+1;
            //echo $trimquestionidsa;
            //echo 'checking';    
            */

            $questionidsa =  $QuizquestionID->question_id;
            $trimquestionidsa =  $QuizquestionID->id;
        } else {
            $questionidsa = 0;
        }

        $quizDetails = QuizDetails::join('course_basic_infos', 'quiz_details.course_id', '=', 'course_basic_infos.id')
            ->join('course_lesson_basic_infos', 'quiz_details.lesson_id', '=', 'course_lesson_basic_infos.id')
            ->where('quiz_details.question_id', $questionidsa)
            ->select('quiz_details.*', 'course_basic_infos.course_name as course_name', 'course_lesson_basic_infos.lesson_name as lesson_name')
            ->first();

        $quizQuestions = QuizQuestion::where('quiz_id', $trimquestionidsa)->get();


        /*
        return $courseContents->content_value = '<div class="text-center m-5">
            <div class="mt-3"><h6>Course Name : {{ $quizDetails->course_name }}</h6></div>
            <div class="mt-3"><h6>Unit Name : {{ $quizDetails->lesson_name }}</h6></div>
            <div class="mt-3"><h6>Total Quiz :  {{ count($quizQuestions) }}</h6></div>
            <div class="mt-3">
            <button onclick="ShowTheQuiz()" class="col-md-3 btn btn-primary mb-2 me-4">Start</button>
            </div>
            </div>';*/
        return $courseContents->content_value = '<div class="text-center m-5"> 
            <div class="mt-3">
            <div class="mt-3"><h6>Course Name : ' . $quizDetails->course_name . '</h6></div>
            <div class="mt-3"><h6>Unit Name : ' . $quizDetails->lesson_name . '</h6></div>
            <div class="mt-3"><h6>Total Quiz :  ' . count($quizQuestions) . '</h6></div>
            <button onclick="ShowTheQuiz(' . $trimquestionidsa . ')" class="col-md-3 btn btn-primary mb-2 me-4">Start</button>
            </div>
            </div>';
    }


    public function learnercoursecontentquestionshow(Request $request)
    {
        $quizQuestions = QuizQuestion::where('quiz_id', $request->content_id)->get();
        $quizDetails = QuizDetails::join('course_basic_infos', 'quiz_details.course_id', '=', 'course_basic_infos.id')
            ->join('course_lesson_basic_infos', 'quiz_details.lesson_id', '=', 'course_lesson_basic_infos.id')
            ->where('quiz_details.id', $request->content_id)
            ->select('quiz_details.*', 'course_basic_infos.course_name as course_name', 'course_lesson_basic_infos.lesson_name as lesson_name')
            ->first();

        if ($quizDetails) {
            $nextQuizDetailQuery = QuizDetails::where('quiz_details.id', '>', $quizDetails->id)
                ->first();
            if ($nextQuizDetailQuery) {
                $nextQuizDetail = $nextQuizDetailQuery->content_id;
            } else {
                $nextQuizDetail = 0;
            }
        } else {
            $nextQuizDetail = 0;
        }

        return view('contents.view-questions', compact('quizQuestions', 'quizDetails', 'nextQuizDetail'));
        return $courseContents->content_value = '<div class="text-center m-5"> 
            <div class="mt-3"> 
            <div class="mt-3"><h6>' . print_r($quizQuestions) . '</h6></div>
            </div>
            </div>';
    }

    public function learnercoursecontentquizsubmit(Request $request)
    {
        //$quizQuestions = QuizQuestion::where('quiz_id', $request->content_id)->get();
        $quizDetails = QuizDetails::join('course_basic_infos', 'quiz_details.course_id', '=', 'course_basic_infos.id')
            ->join('course_lesson_basic_infos', 'quiz_details.lesson_id', '=', 'course_lesson_basic_infos.id')
            ->where('quiz_details.id', $request->content_id)
            ->select('quiz_details.*', 'course_basic_infos.course_name as course_name', 'course_lesson_basic_infos.lesson_name as lesson_name')
            ->first();

        $course_id = $quizDetails->course_id;
        $course_category_id = $quizDetails->course_category_id;
        $lesson_id = $quizDetails->lesson_id;
        $content_id = $quizDetails->content_id;
        $user_id = Auth::user()->id;

        $CourseCompletedTracking = CourseCompletedTracking::where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->where('progress', 1)
            ->where('status', 1)
            ->where('complete', 0)
            ->first();

        if ($CourseCompletedTracking) {
            $CourseCompletedTracking->complete = 1;
            $CourseCompletedTracking->save();
        }

        $CourseCompletedTrackingNew = CourseCompletedTracking::where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->where('progress', 0)
            ->where('status', 0)
            ->where('complete', 0)
            ->first();

        if ($CourseCompletedTrackingNew) {
            $CourseCompletedTrackingNew->progress = 1;
            $CourseCompletedTrackingNew->status = 1;
            $CourseCompletedTrackingNew->save();
        }

        //return true;
        /*
            return view('contents.view-questions',compact('quizQuestions','quizDetails'));
            return $courseContents->content_value = '<div class="text-center m-5"> 
            <div class="mt-3"> 
            <div class="mt-3"><h6>'.print_r($quizQuestions).'</h6></div>
            </div>
            </div>';
            */
    }


    ///////////////////////////////////////////////////////Video Contant/////////////////////////////////////////////////////////////

    public function addvideo()
    {
        $categories = CourseCategory::all();
        $courses = CourseBasicInfo::all();
        return view('contents.add-video', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses', 'categories'));
    }

    // public function uploadvideo(Request $request)
    // {

    //     if ($request->hasFile('videocontent')) {
    //         $video = $request->file('videocontent');
    //         $videoname = $video->getClientOriginalName();
    //         $video->move(public_path('video_content'), $videoname);
    //         $videonamepath = 'video_content/' . $videoname;
    //         $createVideo = new VideoContent();
    //         $createVideo->title = '';
    //         $createVideo->course_id = $request->input('course_id');
    //         $createVideo->video_url = $videonamepath;
    //         $createVideo->status = 1;
    //         $createVideo->created_by = Auth::user()->id;
    //         $createVideo->save();
    //         return redirect()->route('video-content')->with('success', 'Video uploaded successfully');
    //     } else {
    //         return redirect()->route('video-content')->with('error', 'Please select video to upload');
    //     }
    // }


    public function uploadvideo(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'title' => 'required',

            'videocontent' => 'nullable|file|mimes:mp4,mov,avi|max:102400|required_without:embeded_url',

            'embeded_url' => 'nullable|required_without:videocontent',
        ], [
            'videocontent.required_without' => 'Please upload a video or enter YouTube URL',
            'embeded_url.required_without'  => 'Please enter YouTube URL or upload a video',
        ]);

        $videonamepath = null;

        // Upload Video File
        if ($request->hasFile('videocontent')) {
            $video = $request->file('videocontent');

            // Better: unique filename (avoid overwrite)
            $videoname = time() . '_' . $video->getClientOriginalName();

            $video->move(public_path('video_content'), $videoname);

            $videonamepath = 'video_content/' . $videoname;
        }

        // Save Data
        $createVideo = new VideoContent();
        $createVideo->title = $request->title;
        $createVideo->course_id = $request->course_id;
        $createVideo->video_url = $videonamepath;

        // ✅ FIXED LINE
        $createVideo->embeded_url = $request->embeded_url;

        $createVideo->status = 1;
        $createVideo->created_by = Auth::id();
        $createVideo->save();

        return redirect()->route('video-content')
            ->with('success', 'Video uploaded successfully');
    }
    public function listvideo()
    {
        //$videos = VideoContent::join('course_basic_infos', 'video_content.course_id', '=', 'course_basic_infos.id')->get();
        $videos = VideoContent::with(['courseBasicInfo'])->get();
        $categories = CourseCategory::all();
        $categoryArray = [];
        foreach ($categories as $category) {
            $categoryArray[$category->id] = $category->category_name;
        }
        //print_r($videos->courseBasicInfo);
        return view('contents.video-list', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('videos', 'categoryArray'));
    }



    public function ChangeStatusVideoContent(Request $request)
    {
        /*
        $courseBasicInfo = CourseBasicInfo::where('id', $request->course_id)->first();
        $courseBasicInfo->status = $request->status;
        $courseBasicInfo->save();
        */

        $content = VideoContent::findOrFail($request->id);
        $content->status = $request->status;
        $content->save();

        /*
        if( $request->status==1){
            $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 0,'complete' => 0,'status' => 0]);

        } else {
            $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 1,'complete' => 1,'status' => 1]);
        }
        */


        return response()->json(['success' => 'Status change successfully.']);
    }



    public function addaudio()
    {
        $categories = CourseCategory::all();
        $courses = CourseBasicInfo::all();
        return view('contents.add-audio', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses', 'categories'));
    }

    public function uploadaudio(Request $request)
    {
        if ($request->hasFile('videocontent')) {
            $video = $request->file('videocontent');
            $videoname = $video->getClientOriginalName();
            $video->move(public_path('audio_content'), $videoname);
            $videonamepath = 'audio_content/' . $videoname;
            $createVideo = new AudioContent();
            $createVideo->title = '';
            $createVideo->course_id = $request->input('course_id');
            $createVideo->video_url = $videonamepath;
            $createVideo->status = 1;
            $createVideo->created_by = Auth::user()->id;
            $createVideo->save();
            return redirect()->route('audio-content')->with('success', 'Audio uploaded successfully');
        } else {
            return redirect()->route('audio-content')->with('error', 'Please select audio to upload');
        }
    }

    public function listaudio()
    {
        //$videos = VideoContent::join('course_basic_infos', 'video_content.course_id', '=', 'course_basic_infos.id')->get();
        $videos = AudioContent::with(['courseBasicInfo'])->get();
        $categories = CourseCategory::all();
        $categoryArray = [];
        foreach ($categories as $category) {
            $categoryArray[$category->id] = $category->category_name;
        }
        //print_r($videos->courseBasicInfo);
        return view('contents.audio-list', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('videos', 'categoryArray'));
    }


    public function ChangeStatusAudioContent(Request $request)
    {
        /*
        $courseBasicInfo = CourseBasicInfo::where('id', $request->course_id)->first();
        $courseBasicInfo->status = $request->status;
        $courseBasicInfo->save();
        */

        $content = AudioContent::findOrFail($request->id);
        $content->status = $request->status;
        $content->save();

        /*
        if( $request->status==1){
            $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 0,'complete' => 0,'status' => 0]);

        } else {
            $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 1,'complete' => 1,'status' => 1]);
        }
        */


        return response()->json(['success' => 'Status change successfully.']);
    }

    public function addresources()
    {
        $categories = CourseCategory::all();
        $courses = CourseBasicInfo::all();
        return view('contents.add-resources', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact('courses', 'categories'));
    }

    public function  getUnitName($courseId, $catId)
    {
        $getUnitdetails = CourseLessonBasicInfo::where('course_basic_info_id', $courseId)->where('course_category_id', $catId)->get();
        // $categoriesInfos = courseLessonBasicInfo::select('course_id')->where('course_basic_info_id', $courseId)->pluck('course_category_id')->toArray(); 
        // $contentInfos=CourseCategory::whereIn('id',$categoriesInfos)->get();
        return response()->json($getUnitdetails);
    }


    public function ChangeStatusContent(Request $request)
    {
        /*
        $courseBasicInfo = CourseBasicInfo::where('id', $request->course_id)->first();
        $courseBasicInfo->status = $request->status;
        $courseBasicInfo->save();
        */

        $content = Content::findOrFail($request->id);
        $content->status = $request->status;
        $content->save();

        if ($request->status == 1) {
            $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 0, 'complete' => 0, 'status' => 0]);
        } else {
            $Tracking = CourseCompletedTracking::where('content_id', $request->id)->update(['progress' => 1, 'complete' => 1, 'status' => 1]);
        }


        return response()->json(['success' => 'Status change successfully.']);
    }



    public function docviewer()
    {

        $filepath = "http://localhost:8000/storage/contents/1BnAtyFtWCK5PEAPxzAYJRimJKWhwIvCe9uIcjT7.pdf";
        return view('courseview.docview', compact('filepath'));
    }
}
