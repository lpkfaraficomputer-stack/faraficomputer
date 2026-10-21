<?php

namespace App\Http\Controllers;

use App\Services\AiAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AgentController extends Controller
{
    public function __construct(private readonly AiAgentService $agent) {}

    /**
     * Terima pertanyaan konsumen dan kembalikan jawaban asisten.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:600'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['nullable', 'in:user,assistant'],
            'history.*.content' => ['nullable', 'string', 'max:2000'],
        ], [
            'message.required' => 'Silakan tulis pertanyaan Anda.',
            'message.max' => 'Pertanyaan terlalu panjang, mohon diringkas.',
        ]);

        $key = 'agent-chat:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'ok' => false,
                'message' => 'Terlalu banyak pertanyaan dalam waktu singkat. Mohon tunggu '.RateLimiter::availableIn($key).' detik lagi ya.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $answer = $this->agent->answer(
            $validated['message'],
            $validated['history'] ?? []
        );

        return response()->json([
            'ok' => true,
            'message' => $answer,
            'ai' => $this->agent->configured(),
        ]);
    }
}
