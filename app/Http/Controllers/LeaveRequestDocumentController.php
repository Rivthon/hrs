<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestDocumentController extends Controller
{
    public function __invoke(LeaveRequest $leaveRequest): StreamedResponse
    {
        Gate::authorize('view', $leaveRequest);
        abort_unless($leaveRequest->supporting_document_path, 404);

        return Storage::disk('local')->download(
            $leaveRequest->supporting_document_path,
            'surat-dokter-'.$leaveRequest->id.'.'.pathinfo($leaveRequest->supporting_document_path, PATHINFO_EXTENSION),
        );
    }
}
