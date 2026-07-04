<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function index(Request $request, $path)
    {
        if(!Storage::exists($path)) {
            abort(404);
        }
        return response(Storage::get($path), 200)
            ->header('Content-Type', Storage::mimeType($path));
    }
}
