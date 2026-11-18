<?php

namespace App\Http\Controllers\Api;

use App\Exports\TodosExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterTodoRequest;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class TodoController extends Controller
{
    public function index(FilterTodoRequest $request): JsonResponse
    {
        $todos = Todo::query()
            ->filter($request->validated())
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => TodoResource::collection($todos),
            'summary' => [
                'total_todos' => $todos->count(),
                'total_time_tracked' => round($todos->sum('time_tracked'), 2),
            ],
        ]);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $todo = Todo::create($request->validated());

        return response()->json([
            'message' => 'Todo created successfully.',
            'data' => new TodoResource($todo),
        ], Response::HTTP_CREATED);
    }

    public function export(FilterTodoRequest $request): BinaryFileResponse
    {
        $fileName = 'todo-report-'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new TodosExport($request->validated()),
            $fileName,
            ExcelWriter::XLSX,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}