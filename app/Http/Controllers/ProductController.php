<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * 📦 Lấy danh sách tất cả sản phẩm (Có hình ảnh, phân trang)
     */
    public function getProducts()
    {
        $products = Product::where('status', 'active') // Chỉ lấy sản phẩm còn bán
            ->paginate(10); // Phân trang, 10 sản phẩm mỗi trang

        return response()->json([
            'status' => 'success',
            'products' => $products
        ], 200);
    }

    /**
     * 🍿 Lấy danh sách combo bắp nước
     */
    public function getCombos()
    {
        $combos = Product::where('status', 'available')->get();
        return response()->json([
            'status' => 'success',
            'combos' => $combos
        ], 200);
    }
    

    /**
     * 🔍 Lấy chi tiết một sản phẩm theo ID
     */
    public function getProductDetail($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }

        return response()->json([
            'status' => 'success',
            'product' => $product
        ], 200);
    }
}