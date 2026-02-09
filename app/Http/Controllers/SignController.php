<?php

namespace App\Http\Controllers;

use App\Services\OnnxModelManager;
use Illuminate\Http\Request;

class SignController extends Controller
{
    protected OnnxModelManager $manager;

    // Full labels map
    protected array $labels_map = [
        1 => '0', 2 => '1', 3 => '2', 4 => '3', 5 => '4', 6 => '5',
        160 => 'يأكل', 161 => 'يشرب', 162 => 'ينام', 163 => 'يستيقظ', 164 => 'يسمع',
        165 => 'يسكت', 166 => 'يشم', 167 => 'يصعد', 168 => 'ينزل', 169 => 'يفتح',
        170 => 'يقفل ( يغلق )', 171 => 'يبني', 172 => 'يكسر', 173 => 'يمشي', 174 => 'يحب',
        175 => 'يكره', 176 => 'يشوي', 177 => 'يحرث', 178 => 'يزرع', 179 => 'يسقي',
        180 => 'يحصد', 181 => 'يفكر', 182 => 'يساعد', 183 => 'يدخن', 184 => 'يدعم',
        185 => 'يختار', 186 => 'ينادي', 187 => 'يتنامى', 188 => 'يصبغ', 189 => 'يقف',
        190 => 'يستحم', 191 => 'يدخل', 192 => 'أسرة', 193 => 'جدة', 194 => 'جد',
        195 => 'أب', 196 => 'أم', 197 => 'أخت', 198 => 'أخ', 199 => 'بنت',
        200 => 'رضيع', 201 => 'توأم', 202 => 'رجل', 203 => 'شاب', 204 => 'شابة',
        205 => 'حفيد', 206 => 'زواج', 207 => 'حمل', 208 => 'ولادة', 209 => 'عم',
        210 => 'عمة', 211 => 'خال', 212 => 'خالة', 213 => 'ابن الأخ', 214 => 'ابن الأخت',
        215 => 'ابن العم', 216 => 'ابن', 217 => 'ابنة',
        223 => 'طفل', 224 => 'جميل', 225 => 'قبيح', 226 => 'طويل', 227 => 'قصير',
        228 => 'نحيف', 229 => 'سمين', 230 => 'غني', 231 => 'فقير', 232 => 'محبط',
        233 => 'مشمئز', 234 => 'مرتبك', 235 => 'قلق', 236 => 'مشوش', 237 => 'خائف',
        238 => 'سعيد (مسرور)', 239 => 'حزين', 240 => 'شجاع', 241 => 'جبان', 242 => 'طموح',
        243 => 'معجب',
        273 => 'أمام', 274 => 'بجانب', 275 => 'بعيد', 276 => 'بين', 277 => 'تحت',
        278 => 'حول', 279 => 'خارج', 280 => 'خلف', 281 => 'داخل', 282 => 'فوق',
        283 => 'قريب', 284 => 'من خلال', 285 => 'هنا', 286 => 'هناك', 287 => 'يسار',
        288 => 'يمين', 289 => 'أهلا وسهلاً', 290 => 'السلام عليكم', 291 => 'تفضل',
        292 => 'جار', 293 => 'شكراً', 294 => 'صديق',
        298 => 'هدية', 299 => 'بيت',
        485 => 'طيار', 486 => 'جندي', 487 => 'حلاق', 488 => 'صباغ', 489 => 'رجل إطفاء / دفاع مدني',
        490 => 'نجار', 491 => 'معلم / مدرس', 492 => 'طباخ', 493 => 'فلاح', 494 => 'موظف', 495 => 'أمين صندوق'
    ];

    public function __construct(OnnxModelManager $manager)
    {
        $this->manager = $manager;
    }

    public function predict(Request $request): array
    {
        $request->validate([
            'model' => 'required|string',
            'sequence' => 'required|array',
        ]);

        $name = $request->input('model');
        $sequence = $request->input('sequence');

        // Convert sequence to float
        $sequence = array_map(fn($row) => array_map(fn($x) => (float)$x, $row), $sequence);
        $sequence3D = [$sequence]; // (1, T, F)

        // Use cached model
        $model = $this->manager->getModel($name);

        // Predict
        $output = $model->predict(['input' => $sequence3D]);

        // Get logits
        $logits = $output['logits'][0];
        $predictedIndex = array_search(max($logits), $logits);

        // Map to word directly
        $predictedWord = $this->labels_map[$predictedIndex] ?? null;

        return [
            "class_index" => $predictedIndex,
            "predicted_word" => $predictedWord
        ];
    }
}
