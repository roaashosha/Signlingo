<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\LessonResource;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    public function showCategories(Request $request)
    {
        $name = $request->query('name');
        $categories = Category::filter($name)->get();
        if ($categories->isEmpty() && $name) {
            // No categories found for this search
            return $this->ApiResponse(null, "No categories found for '{$name}'", 404);
        }
        return $this->ApiResponse(CategoryResource::collection($categories), "Categories returned successfully!", 200);
    }

    public function lessonsByCategory($id)
    {
        $user = auth()->user(); // could be null if not logged in

        $category = Category::with(['lessons' => function ($query) use ($user) {
            if ($user) {
                // Eager load progress info for this user
                $query->with(['users' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            }
        }])->find($id);

        if (!$category) {
            return $this->ApiResponse(null, "Category not found", 404);
        }

        if ($category->lessons->isEmpty()) {
            return $this->ApiResponse(null, 'No lessons found for this category', 404);
        }

        // Map lessons and add 'done' flag
        $lessons = $category->lessons->map(function ($lesson) use ($user) {
            $done = $user ? $lesson->users->contains($user->id) : false;

            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'link' => $lesson->link,
                'done' => $done
            ];
        });

        return $this->ApiResponse(
            $lessons,
            "Lessons returned successfully!",
            200
        );
    }


    public function progressPerCategory()
    {
        $user = auth()->user();

        // get all categories with lessons
        $categories = Category::with('lessons')->get();

        $progress = $categories->map(function ($category) use ($user) {
            $totalLessons = $category->lessons->count();

            // count lessons completed by this user
            $completedLessons = $category->lessons()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                    ->where('done', 1);
                })
                ->count();

            // calculate percentage
            $percentage = $totalLessons > 0 ? (($completedLessons / $totalLessons) * 100) : 0;

            return [
                'user_id'=>$user->id,
                'category_id' => $category->id,
                'progress' => $percentage
            ];
        });

        return $this->ApiResponse($progress, "Progress per category returned successfully!", 200);
    }


    public function lessonsProgressPerCategory()
    {
        $user = auth()->user();

        $categories = Category::with('lessons')->get();

        $data = $categories->map(function ($category) use ($user) {

            $totalLessons = $category->lessons->count();

            $completedLessons = $category->lessons()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                    ->where('progress.done', 1);
                })
                ->count();

            return [
                'category_id' => $category->id,
                'progress' => "{$completedLessons} / {$totalLessons}",
                'user_id'=>$user->id
            ];
        });

        return $this->ApiResponse(
            $data,
            "Lessons progress per category returned successfully",
            200
        );
    }

    public function viewedLessons($categoryId){
        $user = auth()->user();

        $category = Category::with(['lessons' => function($query) use ($user) {
            $query->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->where('progress.done', 1); // only completed
            });
        }])->find($categoryId);

        if (!$category) {
            return $this->ApiResponse(null, "Category not found", 404);
        }

        if ($category->lessons->isEmpty()) {
            return $this->ApiResponse(null, "No finished lessons for this category", 404);
        }

        return $this->ApiResponse(
            LessonResource::collection($category->lessons),
            "Finished lessons returned successfully",
            200
        );
    }

    

    
}
