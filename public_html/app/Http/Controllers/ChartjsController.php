<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseBasicInfo;

class ChartjsController extends Controller
{
    public function DashboardCourseCharts()
    {
        $offline = CourseBasicInfo::join('course_categories', 'course_categories.id', '=', 'course_category_id')
            ->where('course_categories.category_name', '=', 'Offline')
            ->count();
        $virtual = CourseBasicInfo::join('course_categories', 'course_categories.id', '=', 'course_category_id')
            ->where('course_categories.category_name', '=', 'Virutal')
            ->count();
        $elearning = CourseBasicInfo::join('course_categories', 'course_categories.id', '=', 'course_category_id')
            ->where('course_categories.category_name', '=', 'E-learning')
            ->count();
        $data = [
            'coursecount'=>$offline,
            'virtualcount'=>$virtual,
            'elearningcount'=>$elearning
        ];

        return response()->json($data);
    }
}
