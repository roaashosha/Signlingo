<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizUser;
use App\Http\Resources\QuizResource;
use App\Http\Resources\QuizUserResource;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
             return $this->ApiResponse(null,"Quiz not found!",404);
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

        // Get user's quiz attempt
        $userQuiz = $userQuiz = QuizUser::where('id', $userQuizId)
                    ->where('user_id', $user->id)
                    ->where('quiz_id', $quizId)->latest('created_at')
                    ->first();
        if (!$userQuiz) {
            return $this->ApiResponse(null, "This attempt not found!", 404);
        }

        // Check if answers array exists
        if (!$answers || count($answers) === 0) {
            return $this->ApiResponse(null, "All answers should be filled!", 400);
        }

        // Get quiz with questions
        $quiz = Quiz::with('questions')->find($quizId);
        if (!$quiz) {
            return $this->ApiResponse(null, "Quiz not found!", 404);
        }

        // // Get user's quiz attempt
        // $userQuiz = $userQuiz = QuizUser::where('id', $userQuizId)
        //             ->where('user_id', $user->id)
        //             ->where('quiz_id', $quizId)
        //             ->first();
        // if (!$userQuiz) {
        //     return $this->ApiResponse(null, "This attempt not found!", 404);
        // }

        // Check if quiz already submitted
        if ($userQuiz->status) {
            return $this->ApiResponse(null, "This attempt is already done!", 400);
        }

        $answersByQuestionId = [];

        foreach ($quiz->questions as $index => $question) {
            $key = $index + 1; // frontend index
            if (isset($answers[$key])) {
                $answersByQuestionId[$question->id] = $answers[$key];
            }
        }

        //store answers in cache
        // Cache::put("quiz_answers_user_{$userQuizId}",$answers,now()->addMinutes(30));
        Cache::put("quiz_answers_user_{$userQuizId}",$answersByQuestionId,now()->addMinutes(30));


        // Calculate time taken in seconds
        $timeTakenSeconds = now()->diffInSeconds($userQuiz->created_at);
        $quizDurationSeconds = $quiz->duration_mins * 60; // convert minutes to seconds
        $isTimeUp = $timeTakenSeconds >= $quizDurationSeconds;

        // Calculate score
        $score = 0;
        $questions = $quiz->questions->values(); // reindex from 0
        foreach ($questions as $index => $question) {
            $key = $index + 1; // matches front-end keys 1, 2, 3...
            if (!$isTimeUp && (!isset($answers[$key]) || empty($answers[$key]))) {
                return $this->ApiResponse(null, "All questions must be answered!", 400);
            }

            if (isset($answers[$key]) && $answers[$key] == $question->answer) {
                $score++;
            }
        }

        // Calculate percentage and feedback
        $totalQuestions = $quiz->questions->count();
        $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
        $feedback = $this->getFeedback($percentage);

        // Convert time taken to minutes with 2 decimals
        $timeTakenMins = round($timeTakenSeconds / 60, 2);

        $shareToken = Str::random(32);

        // Update user's quiz attempt
        $userQuiz->update([
            "status" => true,
            "score" => $score,
            "time_mins" => $timeTakenMins,
            "share_token"=>$shareToken
        ]);

        // Return response
        return $this->ApiResponse([
            "attemp"=>$userQuiz,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'percentage' => $percentage,
            'feedback' => $feedback,
            "time_mins" => $timeTakenMins,
            'time_up' => $isTimeUp,
            "share_token"=>$shareToken
        ], "The quiz is submitted successfully!", 200);
    }

    public function reviewAnswers($quizId, $userQuizId)
    {
        $quiz = Quiz::with('questions')->find($quizId);
        if (!$quiz) {
            return $this->ApiResponse(null, "Quiz not found!", 404);
        }

        // Get cached answers
        $userAnswers = Cache::get("quiz_answers_user_{$userQuizId}", []);
        

        $data = $quiz->questions->map(function ($q) use ($userAnswers,$quiz) {

            $options = [
                1 => $q->option_1,
                2 => $q->option_2,
                3 => $q->option_3,
                4 => $q->option_4,
            ];

            $userAnswer =  $userAnswers[$q->id]  ?? null;


            return [
                "question_id" => $q->id,
                "title" => $q->title,
                "options" => $options,

                // user info
                "user_answer" => $userAnswer,
                // "user_answer_text" => $userAnswer ? $options[$userAnswer] : null,

                // correct info
                "correct_answer" => $q->answer,
                // "correct_answer_text" => $options[$q->answer],

                "is_correct" => $userAnswer == $q->answer
            ];
        });

        return $this->ApiResponse(
            $data,
            "Review answers returned successfully!",
            200
        );
    }  

    public function sharedResult($token)
    {
        $userQuiz = QuizUser::with('quiz')
            ->where('share_token', $token)
            ->first();

        if (!$userQuiz) {
            return $this->ApiResponse(null, "Invalid or expired link", 404);
        }

        return $this->ApiResponse([
            "quiz_title" => $userQuiz->quiz->category->name,
            "score" => $userQuiz->score,
            "percentage" => round(
                ($userQuiz->score / $userQuiz->quiz->questions()->count()) * 100,
                2
            ),
            "time_mins" => $userQuiz->time_mins,
            "submitted_at" => $userQuiz->updated_at->toDateTimeString(),
        ], "Shared quiz result", 200);
    }


    public function generateShareLink($userQuizId){
        $userQuiz = QuizUser::where('id', $userQuizId)
        ->where('user_id', auth()->id())
        ->first();

        if (!$userQuiz) {
            return $this->ApiResponse(null, "Attempt not found", 404);
        }

        if (!$userQuiz->share_token) {
            $userQuiz->share_token = Str::random(32);
            $userQuiz->save();
        }

        $link = url("/shared-result/{$userQuiz->share_token}");

        return $this->ApiResponse([
            'share_link' => $link
        ], "Share link generated", 200);
    }

}
