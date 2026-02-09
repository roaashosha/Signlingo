<?php
require __DIR__.'/vendor/autoload.php';

use OnnxRuntime\InferenceSession;

$modelPath = __DIR__ . '/public/models/sign.onnx';

echo "Loading model...\n";
$session = new InferenceSession($modelPath);
echo "Model loaded successfully!\n";

$input = [
    'input' => array_fill(0, 30, array_fill(0, 126, 0.1)) // 30×126 dummy FP32 input
];

echo "Running prediction...\n";
$outputs = $session->run($input);
print_r($outputs);
