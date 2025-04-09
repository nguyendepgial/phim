<?php

// app/Http/Controllers/GenreController.php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * Lấy tất cả thể loại phim.
     */
    public function index()
    {
        $genres = Genre::all();  // Lấy tất cả thể loại
        return response()->json($genres);
    }
    
    /**
     * Lấy thể loại theo ID.
     */
    public function show($id)
    {
        $genre = Genre::find($id);  // Lấy thể loại theo ID

        if (!$genre) {
            return response()->json(['error' => 'Thể loại không tồn tại'], 404);
        }

        return response()->json($genre);
    }

    /**
     * Tạo mới một thể loại.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $genre = Genre::create($request->all());
        return response()->json($genre, 201);
    }

    /**
     * Cập nhật thể loại.
     */
    public function update(Request $request, $id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['error' => 'Thể loại không tồn tại'], 404);
        }

        $genre->update($request->all());
        return response()->json($genre);
    }

    /**
     * Xóa thể loại.
     */
    public function destroy($id)
    {
        $genre = Genre::find($id);

        if (!$genre) {
            return response()->json(['error' => 'Thể loại không tồn tại'], 404);
        }

        $genre->delete();
        return response()->json(['message' => 'Thể loại đã bị xóa']);
    }
}

