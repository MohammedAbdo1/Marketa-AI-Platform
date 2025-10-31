<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\PythonAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIDiagnosticsController extends Controller
{
    protected PythonAIService $aiService;

    public function __construct(PythonAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function testText(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|min:1',
        ]);

        $result = $this->aiService->testTextImprove($validated['text']);
        return response()->json(['success' => true, 'data' => $result]);
    }

    public function testImage(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'nullable|string',
        ]);

        $result = $this->aiService->testGenerateImage($validated['prompt'] ?? '');
        return response()->json(['success' => true, 'data' => $result]);
    }
}










