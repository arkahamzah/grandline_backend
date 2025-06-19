<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/admin', function () {
    return view('admin.dashboard');
});
Route::get('/', function () {
    return view('welcome');
});

// CUSTOM ROUTE UNTUK SERVE IMAGES DENGAN PROPER HEADERS (ESPECIALLY GIF)
Route::get('/storage/profile_images/{filename}', function ($filename) {
    $path = storage_path('app/public/profile_images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    // Detect MIME type berdasarkan extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    // Special headers untuk GIF animation
    $headers = [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000', // 1 year cache
        'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
    ];
    
    // Untuk GIF, tambahkan headers khusus
    if ($extension === 'gif') {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['Accept-Ranges'] = 'bytes';
    }
    
    return Response::file($path, $headers);
})->where('filename', '[^/]+');

// Route untuk series covers
Route::get('/storage/series/{filename}', function ($filename) {
    $path = storage_path('app/public/series/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    $headers = [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
        'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
    ];
    
    if ($extension === 'gif') {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['Accept-Ranges'] = 'bytes';
    }
    
    return Response::file($path, $headers);
})->where('filename', '[^/]+');

// Route untuk comic images
Route::get('/storage/comics/{path}', function ($path) {
    $fullPath = storage_path('app/public/comics/' . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }
    
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    $headers = [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
        'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
    ];
    
    if ($extension === 'gif') {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['Accept-Ranges'] = 'bytes';
    }
    
    return Response::file($fullPath, $headers);
})->where('path', '.*');