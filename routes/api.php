<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageUploadController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/upload-images', [ImageUploadController::class, 'upload']);
Route::get('/images', [ImageUploadController::class, 'index']);