import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

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
    assignment: {
        id: number;
        title: string;
        description: string;
        due_date: string;
        max_points: number;
        teacher: {
            id: number;
            name: string;
        };
    };
}

interface Props {
    submission: Submission;
}

const breadcrumbs = (submission: Submission): BreadcrumbItem[] => [
    {
        title: 'School',
        href: '/school',
    },
    {
        title: 'Assignments',
        href: '/school/assignments',
    },
    {
        title: submission.assignment.title,
        href: `/school/assignments/${submission.assignment.id}`,
    },
    {
        title: 'Submission',
        href: `/school/submissions/${submission.id}`,
    },
];

export default function ShowSubmission({ submission }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(submission)}>
            <Head title={`Submission for ${submission.assignment.title} - School`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{submission.assignment.title}</CardTitle>
                        <CardDescription>
                            Student: {submission.student.name} | Submitted:{' '}
                            {new Date(submission.submitted_at).toLocaleDateString()}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div>
                            <h3 className="mb-2 font-semibold">Assignment Description</h3>
                            <p className="text-muted-foreground text-sm">
                                {submission.assignment.description}
                            </p>
                            <div className="text-muted-foreground mt-2 flex gap-4 text-xs">
                                <span>
                                    Due: {new Date(submission.assignment.due_date).toLocaleDateString()}
                                </span>
                                <span>{submission.assignment.max_points} points</span>
                            </div>
                        </div>

                        <div>
                            <h3 className="mb-2 font-semibold">Submission Content</h3>
                            <div className="bg-muted rounded-lg p-4">
                                <p className="whitespace-pre-wrap text-sm">{submission.content}</p>
                            </div>
                        </div>

                        {submission.grade !== null ? (
                            <div className="bg-green-50 dark:bg-green-950/20 rounded-lg p-4">
                                <h3 className="mb-2 font-semibold text-green-900 dark:text-green-100">
                                    Grade: {submission.grade}/{submission.assignment.max_points}
                                </h3>
                                {submission.feedback && (
                                    <div>
                                        <p className="text-xs font-medium text-green-900 dark:text-green-100">
                                            Teacher Feedback:
                                        </p>
                                        <p className="text-muted-foreground mt-1 text-sm">
                                            {submission.feedback}
                                        </p>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="bg-yellow-50 dark:bg-yellow-950/20 rounded-lg p-4">
                                <p className="font-medium text-yellow-900 dark:text-yellow-100">
                                    This submission is pending review from the teacher.
                                </p>
                            </div>
                        )}

                        <div className="flex gap-2">
                            <Link href={`/school/assignments/${submission.assignment.id}`}>
                                <button className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                                    View Assignment
                                </button>
                            </Link>
                            <Link href="/school">
                                <button className="rounded-md border px-4 py-2 text-sm font-medium hover:bg-muted">
                                    Back to Dashboard
                                </button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

