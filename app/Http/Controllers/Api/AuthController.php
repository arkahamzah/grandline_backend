<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:3|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    public function updateProfileImage(Request $request): JsonResponse
    {
        try {
            // VALIDASI SEDERHANA
            if (!$request->hasFile('profile_image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image file uploaded'
                ], 400);
            }

            $file = $request->file('profile_image');
            
            // VALIDASI FILE
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file'
                ], 400);
            }

            // CEK EXTENSION - SUPPORT GIF DAN WEBP
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only JPG, JPEG, PNG, GIF, and WEBP files are allowed'
                ], 400);
            }

            // CEK MIME TYPE UNTUK KEAMANAN EXTRA
            $allowedMimeTypes = [
                'image/jpeg',
                'image/jpg', 
                'image/png',
                'image/gif',
                'image/webp'
            ];
            
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Only image files are allowed.'
                ], 400);
            }

            // CEK SIZE (MAX 10MB untuk GIF, 5MB untuk lainnya)
            $maxSize = $extension === 'gif' ? 10 * 1024 * 1024 : 5 * 1024 * 1024;
            
            if ($file->getSize() > $maxSize) {
                $maxSizeMB = $extension === 'gif' ? '10MB' : '5MB';
                return response()->json([
                    'success' => false,
                    'message' => "File size too large. Maximum {$maxSizeMB} for {$extension} files"
                ], 400);
            }

            $user = $request->user();

            // HAPUS FOTO LAMA
            if ($user->profile_image) {
                $oldImagePath = 'public/profile_images/' . $user->profile_image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                    \Log::info('Old profile image deleted: ' . $oldImagePath);
                }
            }

            // IMPROVED: SIMPAN FOTO BARU dengan timestamp untuk uniqueness
            $timestamp = time();
            $randomString = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 6);
            $filename = "{$timestamp}_{$user->id}_{$randomString}.{$extension}";
            
            // Store file
            $file->storeAs('public/profile_images', $filename);
            
            // Verify file was stored
            $storedPath = 'public/profile_images/' . $filename;
            if (!Storage::exists($storedPath)) {
                throw new \Exception('Failed to store image file');
            }

            // UPDATE USER dengan filename baru
            $user->update(['profile_image' => $filename]);

            // FORCE REFRESH USER DATA dari database
            $user = $user->fresh();

            // LOG UNTUK DEBUG
            \Log::info('Profile image updated successfully', [
                'user_id' => $user->id,
                'old_filename' => $user->getOriginal('profile_image'),
                'new_filename' => $filename,
                'extension' => $extension,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => $user->profile_image_url,
                'storage_path' => $storedPath,
                'file_exists' => Storage::exists($storedPath)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'data' => $user,
                'debug' => [
                    'filename' => $filename,
                    'url' => $user->profile_image_url,
                    'timestamp' => $timestamp
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile image upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:3|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}