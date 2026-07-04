<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class CourseCategoryController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $categories = CourseCategory::all();
    
        return view('course.category', ['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact("categories"));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        return view('course.create');
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);
        $userId = Auth::user()->id;

        $findCategoryCount = CourseCategory::where('category_name', $request->input('name'))->count();

        if($findCategoryCount==0){
        $category = new CourseCategory();
        $category->category_name = $request->input('name');
        $category->created_by = $userId;
        // Set other properties as needed
        $category->save();

        return redirect()->route('course-category')->with('success', 'Category created successfully');
        } else {
            
        return redirect()->route('course-category')->with('error', 'Category already exist. Please check and try again.');
        }
    }

    // Display the specified resource.
    public function show($id)
    {
        $category = CourseCategory::findOrFail($id);
        return view('course.show', compact('category'));
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        $editCategory = CourseCategory::findOrFail($id);
        $categories = CourseCategory::all();
        $Model = true;
        return view('course.category',['Model' => $Model,'title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'], compact("categories","editCategory"));
    }

    // Update the specified resource in storage.
    public function update(Request $request)
    {
        
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
        ]);
        $id = $request->category_id;
        $findCategoryCount = CourseCategory::where('category_name', $request->input('name'))->where('id','!=',$id)->count();

        if($findCategoryCount==0){
        $category = CourseCategory::findOrFail($id);
        $category->category_name = $request->input('name');
        // Update other properties as needed
        $category->save();

        return redirect()->route('course-category')->with('success', 'Category updated successfully');
        } else {
            
        return redirect()->route('course-category')->with('error', 'Category Name Already Exist');
        }
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $category = CourseCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('course-category')->with('success', 'Category deleted successfully');
    }
}
    

