<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        // Show files related to tasks this manager created
        $query = Attachment::with(['task', 'uploadedBy.employee'])
            ->whereHas('task', fn($q) => $q->where('created_by_user_id', Auth::id()))
            ->where('is_deleted', false)
            ->orderByDesc('uploaded_at');

        if ($search = $request->get('search')) {
            $query->where('file_name', 'like', "%{$search}%");
        }

        $files     = $query->paginate(20)->withQueryString();
        $fileTypes = collect();

        return view('manager.files.index', compact('files', 'fileTypes'));
    }

    public function download(Attachment $attachment)
    {
        abort_if($attachment->is_deleted, 404);
        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
