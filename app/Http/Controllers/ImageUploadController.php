<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{

    public function index()
    {
        // Log the path being used
        $storagePath = storage_path('app/public/uploads');
        // \Log::info('Storage Path: ' . $storagePath);
    
        $files = glob($storagePath . '/*');  // Use PHP's glob function to find files
    
        // \Log::info('Retrieved Files: ' . json_encode($files));
    
        if (empty($files)) {
            return response()->json([
                'success' => false,
                'message' => 'No files found in uploads directory.',
            ]);
        }
    
        // Process files to return URLs
        $images = array_map(function ($file) {
            return [
                'name' => basename($file),
                'url' => Storage::url('uploads/' . basename($file)),
            ];
        }, $files);
    
        return response()->json($images);
    }
    
    public function upload(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation rules for images
        ]);
    
        $uploadedImages = [];
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Get the original filename
                $imageName = $image->getClientOriginalName();
                // Store the image in the 'uploads' folder
                $path = $image->store('uploads', 'public'); 
    
                // Log the original image name and stored path
                \Log::info('Uploaded File Name: ' . $imageName);
                \Log::info('Uploaded File Path: ' . $path);
    
                // Add the image name and path to the response
                $uploadedImages[] = [
                    'name' => $imageName,
                    'path' => $path
                ];
            }
        } else {
            \Log::error('No files received for upload.');
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully!',
            'data' => $uploadedImages,
        ]);
    }
}
