<?php

namespace Modules\School\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\School\Http\Requests\GradeSubmissionRequest;
use Modules\School\Http\Requests\StoreSubmissionRequest;
use Modules\School\Models\Assignment;
use Modules\School\Models\Submission;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request, Assignment $assignment): RedirectResponse
    {
        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $request->user()->id,
            ],
            [
                'content' => $request->input('content'),
                'submitted_at' => now(),
            ]
        );

        return redirect()
            ->route('school.assignments.show', $assignment)
            ->with('success', 'Submission saved successfully.');
    }

    public function show(Submission $submission): Response
    {
        $submission->load(['assignment', 'student']);

        return Inertia::render('School/Submission/Show', [
            'submission' => $submission,
        ]);
    }

    public function grade(GradeSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $this->authorize('grade', $submission);

        $submission->update([
            'grade' => $request->input('grade'),
            'feedback' => $request->input('feedback'),
        ]);

        return back()->with('success', 'Submission graded successfully.');
    }
}
