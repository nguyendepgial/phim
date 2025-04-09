<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products'; // Đảm bảo lấy đúng bảng

    protected $fillable = [
        'name',
        'description',
        'price',
        'image', // Chỉ lưu 1 ảnh, không cần bảng product_images
        'status', // available / unavailable
    ];
}
