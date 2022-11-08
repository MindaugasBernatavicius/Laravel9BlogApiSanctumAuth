<?php

namespace App\Http\Controllers;

use App\Models\Blogpost;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BlogpostController extends Controller
{
    public function index(Request $request)
    {
        if ($request->embed === "comments")
            return Blogpost::with('comments')->get();
        return Blogpost::all();
    }

    public function store(Request $request)
    {
        error_log($request);
        $request->validate([
            'title' => 'required|unique:blogposts,title',
            'text' => 'required|max:255',
            'image_name' => 'required|max:255'
        ]);
        return Blogpost::create($request->all());
    }

    public function show($id, Request $request)
    {
        if ($request->embed === "comments")
            return Blogpost::with('comments')->find($id);
        return Blogpost::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|unique:blogposts,title,' . $id . ',id',
            'text' => 'required|max:255',
        ]);
        $blogpost = Blogpost::find($id);
        $blogpost->update($request->all());
        return $blogpost;
    }

    public function destroy($id)
    {
        return Blogpost::destroy($id);
    }

    public function storePostComment($id, Request $request)
    {
        $request->validate(['text' => 'required|max:255']);
        return Blogpost::find($id)
            ->comments()
            ->save(Comment::create($request->all()));
    }

    public function storePostImage(Request $request)
    {
        $file = $request->file('file');
        if(File::exists(public_path() . '/storage/' . $request['fileName']))
            return response()->json([
                "success" => false,
                "message" => "File with this name already exists."
            ], 400);
        
        $imageName = $request['fileName']; // .'.'.$file->extension();
        $imagePath = public_path(). '/storage';
        $file->move($imagePath, $imageName);

        return response()->json([
            "success" => true,
            "message" => "Image has been uploaded successfully."
        ]);
    }
}
