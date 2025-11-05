<?php

namespace Modules\School\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\School\Http\Requests\StoreAssignmentRequest;
use Modules\School\Http\Requests\UpdateAssignmentRequest;
use Modules\School\Models\Assignment;

class AssignmentController extends Controller
{
    public function index(): Response
    {
        $assignments = Assignment::query()
            ->with('teacher')
            ->withCount('submissions')
            ->latest()
            ->get();

        return Inertia::render('School/Assignment/Index', [
            'assignments' => $assignments,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Assignment::class);

        return Inertia::render('School/Assignment/Create');
    }

    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        $assignment = Assignment::create([
            'teacher_id' => $request->user()->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'due_date' => $request->input('due_date'),
            'max_points' => $request->input('max_points', 100),
        ]);

        return redirect()
            ->route('school.assignments.show', $assignment)
            ->with('success', 'Assignment created successfully.');
    }

    public function show(Assignment $assignment): Response
    {
        $assignment->load([
            'teacher',
            'submissions.student',
        ]);

        return Inertia::render('School/Assignment/Show', [
            'assignment' => $assignment,
        ]);
    }

    public function edit(Assignment $assignment): Response
    {
        $this->authorize('update', $assignment);

        return Inertia::render('School/Assignment/Edit', [
            'assignment' => $assignment,
        ]);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $assignment->update($request->validated());

        return redirect()
            ->route('school.assignments.show', $assignment)
            ->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return redirect()
            ->route('school.assignments.index')
            ->with('success', 'Assignment deleted successfully.');
    }
}
