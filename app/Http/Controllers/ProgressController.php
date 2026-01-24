<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\lesson;

class ProgressController extends Controller
{
    use ApiResponseTrait;
    public function toggleLessonCompletion(Request $request, $lessonId)
    {
        $user = auth()->user();

        // Check if the lesson exists
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            return $this->ApiResponse(null, "Lesson not found", 404);
        }

        // Check if user already has a progress record
        $existing = $lesson->users()->where('user_id', $user->id)->first();

        if ($existing) {
            // Remove progress → toggle off
            $lesson->users()->detach($user->id);
            $status = false;
        } else {
            // Insert progress → toggle on
            $lesson->users()->attach($user->id, ['done'=>1,'created_at' => now()]);
            $status = true;
        }

        return $this->ApiResponse([
            'lesson_id' => $lesson->id,
            'done' => $status
        ], "Lesson progress toggled successfully", 200);
    }
}
