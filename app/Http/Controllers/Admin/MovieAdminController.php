<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MovieAdminController extends Controller
{
    // Hiển thị danh sách phim
    public function index()
    {
        $movies = Movie::with('genres')->paginate(10); // mỗi trang 10 phim
        return response()->json($movies, 200);
    }

    // Thêm phim mới
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'required|string',
            'poster' => 'required|file|mimes:jpg,jpeg,png,gif,webp',
            'trailer' => 'required|file|mimes:mp4,mov,avi,mkv,webm',
            'release_date' => 'required|date',
            'duration' => 'required|integer',
            'rating' => 'required|numeric',
            'status' => 'required|string',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Lưu file poster và trailer
        $posterFile = $request->file('poster');
        $posterName = $posterFile->getClientOriginalName();
        $posterFile->move(public_path('uploads'), $posterName);

        $trailerFile = $request->file('trailer');
        $trailerName = $trailerFile->getClientOriginalName();
        $trailerFile->move(public_path('uploads'), $trailerName);

        // Lưu vào DB chỉ tên file (không có /storage/... hay /uploads/)
        $movie = Movie::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'poster' => $posterName,
            'trailer' => $trailerName,
            'release_date' => $request->release_date,
            'duration' => $request->duration,
            'rating' => $request->rating,
            'status' => $request->status,
        ]);

        // Gắn thể loại
        $movie->genres()->attach($request->genres);


        return response()->json($movie, 201);
    }


    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'required|string',
            'poster' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
            'trailer' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm',
            'release_date' => 'required|date',
            'duration' => 'required|integer',
            'rating' => 'required|numeric',
            'status' => 'required|string',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('poster')) {
            $posterFile = $request->file('poster');
            $posterName = $posterFile->getClientOriginalName();
            $posterFile->move(public_path('uploads'), $posterName);
            $movie->poster = $posterName;
        }
        
        if ($request->hasFile('trailer')) {
            $trailerFile = $request->file('trailer');
            $trailerName = $trailerFile->getClientOriginalName();
            $trailerFile->move(public_path('uploads'), $trailerName);
            $movie->trailer = $trailerName;
        }
        

        // Cập nhật các trường thông tin khác
        $movie->title = $request->title;
        $movie->slug = $request->slug;
        $movie->description = $request->description;
        $movie->release_date = $request->release_date;
        $movie->duration = $request->duration;
        $movie->rating = $request->rating;
        $movie->status = $request->status;
        $movie->save();

        // Cập nhật lại danh sách thể loại
        $movie->genres()->sync($request->genres);

        return response()->json($movie, 200);
    }

    // Xóa phim
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }

    // Chi tiết phim
    public function show($id)
    {
        $movie = Movie::with('genres')->find($id);

        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }

        return response()->json($movie);
    }
}
