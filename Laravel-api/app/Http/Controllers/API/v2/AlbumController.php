<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AlbumController extends Controller
{
    public function index()
    {
        $pets = Album::all();
        $pets->map(function ($user) {
            return [
                'user_id' => $user->id,
                'user_email' => $user->user->email,
                'username' => $user->user->name, // album->user()
            ];
        });
        return response()->json([
            'status' => 200,
            'pets' => $pets,
        ]);
    }

    // public function index()
    // {
    //     $cache_key = 'pets_cache';
    //     $maxAge = 120;
    //     if (Cache::has($cache_key)) {
    //         $pets = Cache::get($cache_key);
    //     } else {
    //         $pets = Album::all();
    //         $pets->map(function ($user) {
    //             return [
    //                 'user_id' => $user->id,
    //                 'user_email' => $user->user->email,
    //                 'username' => $user->user->name, // album->user()
    //             ];
    //         });
    //         Cache::put($cache_key, $pets, $maxAge);
    //     }
    //     return response()->json([
    //         'status' => 200,
    //         'pets' => $pets,
    //     ])->header('Cache-Control', "public, max-age=$maxAge");
    // }

    public function store(Request $request)
    {
        if (auth('sanctum')->check()) {
            $validator = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required|max:191',
                    'emotion' => 'required|max:191',
                    'image_pet' => 'required|image|mimes:jpeg,png,jpg|max:15360',
                ],
                [
                    'required' => 'Bạn phải điền :attribute'
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages(),
                ], 422);
            } else {
                $user_id = auth('sanctum')->user()->id;
                $pet = new Album;
                $pet->user_id = $user_id;
                $pet->category_id = $request->input('category_id');
                $pet->emotion = $request->input('emotion');
                if ($request->hasFile('image_pet')) {
                    $file = $request->file('image_pet');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/album/', $filename);
                    $pet->image_pet = 'uploads/album/' . $filename;
                }
                $pet->save();
                return response()->json([
                    'status' => 200,
                    'message' => 'Thêm thú cưng thành công.',
                ]);
            }
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Bạn phải đăng nhập!',
            ], 401);
        }
    }
}
