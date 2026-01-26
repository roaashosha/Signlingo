<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizUser;
use App\Http\Resources\QuizResource;
use App\Http\Resources\QuizUserResource;
use App\Models\QuizQuestion;

class QuizController extends Controller
{
    use ApiResponseTrait;
    public function showQuizes(){
        $quizes = Quiz::all();
        if (!$quizes){
            return $this->ApiResponse(null,"There are no quizes!",404);
        }
        return $this->ApiResponse(QuizResource::collection($quizes),"Quizes returned succesfully!",200);

        
    }

    public function quizQuestions($id){
        
        $questions = QuizQuestion::where('quiz_id',$id)->get();
        if ($questions->isEmpty()){
             return $this->ApiResponse(null,"There are no questions!",404);
        }
        return $this->ApiResponse(QuestionResource::collection($questions),"Questions returned succesfully!",200);
    }

    public function startQuiz($quizId){
        $user = Auth()->user();
        $quiz=Quiz::find($quizId);
        if (!$quiz){
            return $this->ApiResponse(null,"Quiz not found!",404);
        }
        $userQuiz = QuizUser::create([
            "user_id"=>$user->id,
            "quiz_id"=>$quiz->id,
            "score"=>0,
            "time_mins"=>0,
            "created_at"=>now(),
            "status"=>false
        ]);
    //     return $this->ApiResponse(['user_quiz_id' => $userQuiz->id,
    // 'duration_mins' => $quiz->duration_mins],"Quiz started succesfully!",200);

    return $this->ApiResponse(new QuizUserResource($userQuiz),"Quiz started succesfully!",200);
    }

    private function getFeedback($percentage){
        if ($percentage >= 90) 
            return 'Excellent! You have a strong understanding of the material. Keep up the great work and try to challenge yourself with advanced problems!';
        if ($percentage >= 70) 
            return 'Good job! You did well and have a solid grasp of the concepts. Review the areas where you lost points to reach the next level!';
        if ($percentage >= 50) 
            return 'You passed. You have basic understanding, but there’s room for improvement. Focus on the topics you struggled with and practice more.';
        return 'Better luck next time. Don’t get discouraged! Review the lessons carefully, practice consistently, and you’ll improve significantly.';

    }

    public function submitAnswer(Request $request, $quizId)
{
    $user = auth()->user();
    $answers = $request->input('answers');
    $userQuizId = $request->input('user_quiz_id');

    // Check if answers array exists
    if (!$answers || count($answers) === 0) {
        return $this->ApiResponse(null, "All answers should be filled!", 400);
    }

    // Get quiz with questions
    $quiz = Quiz::with('questions')->find($quizId);
    if (!$quiz) {
        return $this->ApiResponse(null, "Quiz not found!", 404);
    }

    // Get user's quiz attempt
    $userQuiz = QuizUser::find($userQuizId);
    if (!$userQuiz) {
        return $this->ApiResponse(null, "This attempt not found!", 404);
    }

    // Check if quiz already submitted
    if ($userQuiz->status) {
        return $this->ApiResponse(null, "This attempt is already done!", 400);
    }

    // Calculate time taken in seconds
    $timeTakenSeconds = now()->diffInSeconds($userQuiz->created_at);
    $quizDurationSeconds = $quiz->duration_mins * 60; // convert minutes to seconds
    $isTimeUp = $timeTakenSeconds >= $quizDurationSeconds;

    // Calculate score
    $score = 0;
    foreach ($quiz->questions as $question) {
        // Before time is up, all answers must be filled
        if (!$isTimeUp && (!isset($answers[$question->id]) || empty($answers[$question->id]))) {
            return $this->ApiResponse(null, "All questions must be answered!", 400);
        }

        // Count correct answers if provided
        if (isset($answers[$question->id]) && $answers[$question->id] == $question->answer) {
            $score++;
        }
    }

    // Calculate percentage and feedback
    $totalQuestions = $quiz->questions->count();
    $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
    $feedback = $this->getFeedback($percentage);

    // Convert time taken to minutes with 2 decimals
    $timeTakenMins = round($timeTakenSeconds / 60, 2);

    // Update user's quiz attempt
    $userQuiz->update([
        "status" => true,
        "score" => $score,
        "time_mins" => $timeTakenMins
    ]);

    // Return response
    return $this->ApiResponse([
        'score' => $score,
        'total_questions' => $totalQuestions,
        'percentage' => $percentage,
        'feedback' => $feedback,
        'time_mins' => $timeTakenMins,
        'time_up' => $isTimeUp
    ], "The quiz is submitted successfully!", 200);
}




    
}
