<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    private function assignedTaskIds()
    {
        $empId = Auth::user()->employee?->employee_id;
        if (!$empId) return collect();
        return TaskAssignment::where('employee_id', $empId)->pluck('task_id');
    }

    public function index(Request $request)
    {
        $taskIds = $this->assignedTaskIds();

        $files = Attachment::with(['task', 'uploadedBy.employee'])
            ->whereIn('task_id', $taskIds)
            ->where('is_deleted', false)
            ->orderByDesc('uploaded_at')
            ->paginate(20);

        return view('employee.files.index', compact('files'));
    }

    public function download(Attachment $attachment)
    {
        abort_if($attachment->is_deleted, 404);
        abort_unless($this->assignedTaskIds()->contains($attachment->task_id), 403);

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
