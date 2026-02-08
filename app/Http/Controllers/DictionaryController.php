<?php

namespace App\Http\Controllers;

use App\Http\Resources\WordResource;
use App\Models\Lesson;
use Illuminate\Http\Request;


class DictionaryController extends Controller
{
    use ApiResponseTrait;

    function normalizeArabicLetter($letter)
    {
        $map = [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ى' => 'ي', // optional, for normalization
            'ؤ' => 'و', // optional
            'ئ' => 'ي', // optional
        ];

        return $map[$letter] ?? $letter;
    }

    public function getWords()
    {
        $user = auth()->user();
        $locale = $user->lang ?? 'en';

        // // Make Laravel use the same locale
        // app()->setLocale($locale);

        $lessons = Lesson::select('name', 'link')->get();

        if ($lessons->isEmpty()) {
            return $this->ApiResponse(null, "Words not found!", 404);
        }

        $wordResources = $lessons->map(function ($lesson) use ($locale) {
            $name = json_decode($lesson->name, true);
            return [
                'word' => $name[$locale] ?? null,
                'link' => $lesson->link,
            ];
        })->filter(fn($item) => !is_null($item['word']));

        $grouped = $wordResources->groupBy(function ($item) use ($locale) {
            $firstChar = mb_substr($item['word'], 0, 1);
            if ($locale === 'ar') {
                $firstChar = $this->normalizeArabicLetter($firstChar);
            }
            return strtoupper($firstChar);
        });

        $result = $grouped->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'word' => $item['word'],
                    'link' => $item['link'],
                ];
            })->toArray();
        });

    return $this->ApiResponse($result, "Words returned successfully!", 200);
}




}
