<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResoureseModel;
use App\Models\CourseModel;
use App\Models\CourseCategory;

class ResourceController extends Controller
{
    public function addresourcesstore(Request $request)
    {

        $request->validate([
            'pdfresourese.*' => 'required|file|max:21000', // You can adjust the validation rules as needed
        ]);

    //echo $request->hasFile('pdfresourese'); exit;

        if ($request->hasFile('pdfresourese')) {
            foreach ($request->file('pdfresourese') as $file) {
                $originalName = $file->getClientOriginalName();
                //$file->storeAs('uploads', $fileName, 'public');
                $file->move(public_path('resources'), $originalName);

                $resource = new ResoureseModel;
                $resource->course_id = $request->course_id;
                $resource->folder = $request->folder;
                $resource->url = $originalName;
                $resource->status = 1;
                $resource->created_by = auth()->user()->id;
                $resource->save();
                
            }
        }

        /*
        if ($request->hasFile('pdfresourese')) 
        {
            $file = $request->file('pdfresourese');
            $originalName = $file->getClientOriginalName();
            // $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('resources'), $originalName);
            $resource = new ResoureseModel;
            $resource->course_id = $request->course_id;
            $resource->url = $originalName;
            $resource->status = 1;
            $resource->created_by = auth()->user()->id;
            $resource->save();
            return redirect()->route('addresources')
                ->with('success', 'Resource added successfully.');
        }
        */

        return redirect()->route('addresources')
                ->with('success', 'Resource added successfully.');
    }

    public function listresources()
    {
        $listresources = ResoureseModel::with(['courseBasicInfo'])->get();
        $categories = CourseCategory::all();
        $categoryArray=[];
        foreach($categories as $category){
            $categoryArray[$category->id]=$category->category_name;
        }
        //$listresources = ResoureseModel::join('course_basic_infos', 'course_basic_infos.id', '=', 'resourse_content.course_id')->get();
        return view('contents.viewresources',['title' => 'User Management LMS | Cogniphere', 'breadcrumb' => 'This Breadcrumb'],compact('listresources','categoryArray'));
    }

    public function downloadresources($id)
    {
        $resource = ResoureseModel::find($id);
        $file = public_path('resources/'.$resource->url);
        return response()->download($file);
    }

    
    public function ChangeStatusResource(Request $request)
    {
        $quizDetails = ResoureseModel::where('id', $request->quiz_id)->first();
        $quizDetails->status = $request->status;
        $quizDetails->save();
        return response()->json(['success' => 'Status change successfully.']);
    }
}
