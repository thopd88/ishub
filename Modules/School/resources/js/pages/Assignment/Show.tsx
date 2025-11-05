import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { FormEventHandler, useState } from 'react';

interface Submission {
    id: number;
    content: string;
    grade: number | null;
    feedback: string | null;
    submitted_at: string;
    student: {
        id: number;
        name: string;
        email: string;
    };
}

interface Assignment {
    id: number;
    title: string;
    description: string;
    due_date: string;
    max_points: number;
    teacher: {
        id: number;
        name: string;
    };
    submissions?: Submission[];
}

interface Props {
    assignment: Assignment;
}

const breadcrumbs = (assignment: Assignment): BreadcrumbItem[] => [
    {
        title: 'School',
        href: '/school',
    },
    {
        title: 'Assignments',
        href: '/school/assignments',
    },
    {
        title: assignment.title,
        href: `/school/assignments/${assignment.id}`,
    },
];

export default function ShowAssignment({ assignment }: Props) {
    const { auth } = usePage<SharedData>().props;
    
    const isTeacher = auth.user.roles?.some((role) => role.name === 'teacher') || false;
    const isStudent = auth.user.roles?.some((role) => role.name === 'student') || false;
    const isOwner = isTeacher && assignment.teacher.id === auth.user.id;

    // Check if current student has already submitted
    const studentSubmission = isStudent && assignment.submissions
        ? assignment.submissions.find(sub => sub.student.id === auth.user.id)
        : null;

    const [gradingSubmission, setGradingSubmission] = useState<number | null>(null);

    const { data: submissionData, setData: setSubmissionData, post: postSubmission, processing: submittingWork, errors: submissionErrors } = useForm({
        content: studentSubmission?.content || '',
    });

    const { data: gradeData, setData: setGradeData, post: postGrade, processing: grading, errors: gradeErrors, reset: resetGrade } = useForm({
        grade: 0,
        feedback: '',
    });

    const submitWork: FormEventHandler = (e) => {
        e.preventDefault();
        postSubmission(`/school/assignments/${assignment.id}/submissions`);
    };

    const gradeSubmission = (submissionId: number) => {
        postGrade(`/school/submissions/${submissionId}/grade`, {
            onSuccess: () => {
                setGradingSubmission(null);
                resetGrade();
            },
        });
    };

    const deleteAssignment = () => {
        if (confirm('Are you sure you want to delete this assignment?')) {
            router.delete(`/school/assignments/${assignment.id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs(assignment)}>
            <Head title={`${assignment.title} - School`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-start justify-between">
                            <div>
                                <CardTitle>{assignment.title}</CardTitle>
                                <CardDescription>
                                    Teacher: {assignment.teacher.name} | Due:{' '}
                                    {new Date(assignment.due_date).toLocaleDateString()} | {assignment.max_points}{' '}
                                    points
                                </CardDescription>
                            </div>
                            {isOwner && (
                                <div className="flex gap-2">
                                    <Link href={`/school/assignments/${assignment.id}/edit`}>
                                        <Button variant="outline" size="sm">
                                            Edit
                                        </Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" onClick={deleteAssignment}>
                                        Delete
                                    </Button>
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="whitespace-pre-wrap">{assignment.description}</div>
                    </CardContent>
                </Card>

                {isStudent && !studentSubmission && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Submit Your Work</CardTitle>
                            <CardDescription>Submit your assignment before the due date</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submitWork} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="content">Your Submission</Label>
                                    <Textarea
                                        id="content"
                                        value={submissionData.content}
                                        onChange={(e) => setSubmissionData('content', e.target.value)}
                                        rows={10}
                                        placeholder="Enter your submission here..."
                                        required
                                    />
                                    {submissionErrors.content && (
                                        <p className="text-sm text-red-600 dark:text-red-400">
                                            {submissionErrors.content}
                                        </p>
                                    )}
                                </div>
                                <Button type="submit" disabled={submittingWork}>
                                    {submittingWork ? 'Submitting...' : 'Submit Work'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {isStudent && studentSubmission && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Your Submission</CardTitle>
                            <CardDescription>
                                Submitted on {new Date(studentSubmission.submitted_at).toLocaleString()}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <h3 className="mb-2 text-sm font-semibold">Your Work</h3>
                                <div className="bg-muted rounded-lg p-4">
                                    <p className="whitespace-pre-wrap text-sm">{studentSubmission.content}</p>
                                </div>
                            </div>

                            {studentSubmission.grade !== null ? (
                                <div className="bg-green-50 dark:bg-green-950/20 rounded-lg p-4">
                                    <div className="mb-2 flex items-center justify-between">
                                        <h3 className="text-sm font-semibold text-green-900 dark:text-green-100">
                                            Grade
                                        </h3>
                                        <p className="text-2xl font-bold text-green-900 dark:text-green-100">
                                            {studentSubmission.grade}/{assignment.max_points}
                                        </p>
                                    </div>
                                    {studentSubmission.feedback && (
                                        <div className="mt-3 border-t border-green-200 pt-3 dark:border-green-800">
                                            <p className="text-xs font-medium text-green-900 dark:text-green-100">
                                                Teacher Feedback:
                                            </p>
                                            <p className="text-muted-foreground mt-1 text-sm">
                                                {studentSubmission.feedback}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="bg-yellow-50 dark:bg-yellow-950/20 rounded-lg p-4">
                                    <div className="flex items-center gap-2">
                                        <div className="bg-yellow-100 dark:bg-yellow-900/40 rounded-full p-2">
                                            <svg
                                                className="size-5 text-yellow-600 dark:text-yellow-400"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <p className="font-medium text-yellow-900 dark:text-yellow-100">
                                                Pending Review
                                            </p>
                                            <p className="text-sm text-yellow-700 dark:text-yellow-300">
                                                Your teacher will grade this submission soon.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {isTeacher && assignment.submissions && assignment.submissions.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Student Submissions ({assignment.submissions.length})</CardTitle>
                            <CardDescription>Review and grade student work</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {assignment.submissions.map((submission) => (
                                    <div key={submission.id} className="rounded-lg border p-4">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <h4 className="font-semibold">{submission.student.name}</h4>
                                                <p className="text-muted-foreground text-sm">
                                                    {submission.student.email}
                                                </p>
                                                <p className="text-muted-foreground mt-1 text-xs">
                                                    Submitted: {new Date(submission.submitted_at).toLocaleString()}
                                                </p>
                                            </div>
                                            {submission.grade !== null && (
                                                <div className="bg-green-50 dark:bg-green-950/20 rounded-md px-3 py-1">
                                                    <p className="text-sm font-medium text-green-900 dark:text-green-100">
                                                        {submission.grade}/{assignment.max_points}
                                                    </p>
                                                </div>
                                            )}
                                        </div>

                                        <div className="bg-muted mt-4 rounded-lg p-4">
                                            <p className="whitespace-pre-wrap text-sm">{submission.content}</p>
                                        </div>

                                        {submission.feedback && (
                                            <div className="bg-blue-50 dark:bg-blue-950/20 mt-3 rounded-md p-3">
                                                <p className="text-xs font-medium text-blue-900 dark:text-blue-100">
                                                    Feedback:
                                                </p>
                                                <p className="text-muted-foreground mt-1 text-sm">
                                                    {submission.feedback}
                                                </p>
                                            </div>
                                        )}

                                        {gradingSubmission === submission.id ? (
                                            <div className="mt-4 space-y-3 rounded-lg border p-4">
                                                <div className="space-y-2">
                                                    <Label htmlFor={`grade-${submission.id}`}>Grade (out of {assignment.max_points})</Label>
                                                    <Input
                                                        id={`grade-${submission.id}`}
                                                        type="number"
                                                        value={gradeData.grade}
                                                        onChange={(e) => setGradeData('grade', parseInt(e.target.value))}
                                                        min="0"
                                                        max={assignment.max_points}
                                                        required
                                                    />
                                                    {gradeErrors.grade && (
                                                        <p className="text-sm text-red-600 dark:text-red-400">
                                                            {gradeErrors.grade}
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="space-y-2">
                                                    <Label htmlFor={`feedback-${submission.id}`}>Feedback</Label>
                                                    <Textarea
                                                        id={`feedback-${submission.id}`}
                                                        value={gradeData.feedback}
                                                        onChange={(e) => setGradeData('feedback', e.target.value)}
                                                        rows={3}
                                                        placeholder="Provide feedback for the student..."
                                                    />
                                                </div>
                                                <div className="flex gap-2">
                                                    <Button
                                                        size="sm"
                                                        onClick={() => gradeSubmission(submission.id)}
                                                        disabled={grading}
                                                    >
                                                        {grading ? 'Saving...' : 'Save Grade'}
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => {
                                                            setGradingSubmission(null);
                                                            resetGrade();
                                                        }}
                                                    >
                                                        Cancel
                                                    </Button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="mt-4">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => {
                                                        setGradingSubmission(submission.id);
                                                        setGradeData({
                                                            grade: submission.grade || 0,
                                                            feedback: submission.feedback || '',
                                                        });
                                                    }}
                                                >
                                                    {submission.grade !== null ? 'Update Grade' : 'Grade Submission'}
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

