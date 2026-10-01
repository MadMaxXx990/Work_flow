<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = Attachment::with(['task', 'uploadedBy.employee'])
            ->where('is_deleted', false)
            ->orderByDesc('uploaded_at');

        if ($search = $request->get('search')) {
            $query->where('file_name', 'like', "%{$search}%");
        }
        if ($type = $request->get('file_type')) {
            $query->where('file_type', $type);
        }

        $files     = $query->paginate(20)->withQueryString();
        $fileTypes = Attachment::where('is_deleted', false)
            ->distinct()->pluck('file_type')->filter()->sort()->values();

        return view('admin.files.index', compact('files', 'fileTypes'));
    }

    public function download(Attachment $attachment)
    {
        abort_if($attachment->is_deleted, 404);

        ActivityLogService::log('download', "Downloaded file: {$attachment->file_name}", 'attachments', $attachment->attachment_id);

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->update(['is_deleted' => true]);

        ActivityLogService::log('delete', "Deleted file: {$attachment->file_name}", 'attachments', $attachment->attachment_id);

        return back()->with('success', 'File removed.');
    }
}
