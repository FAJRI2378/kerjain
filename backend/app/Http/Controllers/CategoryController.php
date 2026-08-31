<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobCategoryResource;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = JobCategory::orderBy('name')->get();

        return JobCategoryResource::collection($categories);
    }
}
