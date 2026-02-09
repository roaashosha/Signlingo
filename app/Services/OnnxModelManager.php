<?php

namespace App\Services;

use OnnxRuntime\Model;

class OnnxModelManager
{
    private array $models = [];

    /**
     * Get a model by name (loads it if not loaded yet)
     */
    public function getModel(string $name): Model
    {
        if (!isset($this->models[$name])) {
            $path = public_path("models/{$name}.onnx"); // public/models folder
            if (!file_exists($path)) {
                throw new \Exception("ONNX model '{$name}' not found at {$path}");
            }
            $this->models[$name] = new Model($path);
        }

        return $this->models[$name];
    }

    
    /**
     * Run prediction using a specific model
     */
    public function predict(string $name, array $sequence): array
    {
        $model = $this->getModel($name);

        return $model->predict([
            'input' => $sequence, // shape: [1, seq_len, features]
        ]);
    }
}
