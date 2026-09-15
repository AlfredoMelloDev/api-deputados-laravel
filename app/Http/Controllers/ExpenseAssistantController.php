<?php

namespace App\Http\Controllers;

use App\Services\Assistant\ExpenseAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseAssistantController extends Controller
{
    public function __invoke(Request $request, ExpenseAssistant $assistant): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:300'],
            'year' => ['nullable', 'integer', 'digits:4', 'min:2008', 'max:'.now()->year],
        ]);

        return response()->json($assistant->answer(
            $validated['question'],
            isset($validated['year']) ? (int) $validated['year'] : null,
        ));
    }
}
