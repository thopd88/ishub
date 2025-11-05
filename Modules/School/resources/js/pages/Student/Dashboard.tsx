import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

interface Submission {
    id: number;
    content: string | null;
    grade: number | null;
    feedback: string | null;
    submitted_at: string | null;
}

interface Assignment {
    id: number;
    title: string;
    description: string;
    due_date: string;
    max_points: number;
    submissions: Submission[];
}

interface Stats {
    pending_assignments: number;
    completed_assignments: number;
}

interface Props {
    assignments: Assignment[];
    stats: Stats;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'School',
        href: '/school',
    },
    {
        title: 'Student Dashboard',
        href: '/school',
    },
];

export default function StudentDashboard({ assignments, stats }: Props) {
    const getAssignmentStatus = (assignment: Assignment) => {
        if (assignment.submissions.length === 0) {
            return { label: 'Not Submitted', color: 'text-red-600 dark:text-red-400' };
        }
        const submission = assignment.submissions[0];
        if (submission.grade !== null) {
            return { label: `Graded: ${submission.grade}/${assignment.max_points}`, color: 'text-green-600 dark:text-green-400' };
        }
        return { label: 'Pending Review', color: 'text-yellow-600 dark:text-yellow-400' };
    };

    const isPastDue = (dueDate: string) => {
        return new Date(dueDate) < new Date();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Student Dashboard - School" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                {/* Stats Section */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardDescription>Pending Assignments</CardDescription>
                            <CardTitle className="text-4xl">{stats.pending_assignments}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardDescription>Completed Assignments</CardDescription>
                            <CardTitle className="text-4xl">{stats.completed_assignments}</CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                {/* Assignments Section */}
                <Card>
                    <CardHeader>
                        <CardTitle>Assignments</CardTitle>
                        <CardDescription>View and submit your assignments</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {assignments.length === 0 ? (
                            <div className="text-muted-foreground py-8 text-center">
                                <p>No assignments available yet.</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {assignments.map((assignment) => {
                                    const status = getAssignmentStatus(assignment);
                                    const pastDue = isPastDue(assignment.due_date);
                                    const submission = assignment.submissions[0];

                                    return (
                                        <div
                                            key={assignment.id}
                                            className="rounded-lg border p-4"
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-3">
                                                        <h3 className="font-semibold">{assignment.title}</h3>
                                                        <span className={`text-sm font-medium ${status.color}`}>
                                                            {status.label}
                                                        </span>
                                                        {pastDue && !submission?.submitted_at && (
                                                            <span className="text-sm font-medium text-red-600 dark:text-red-400">
                                                                Past Due
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-muted-foreground mt-1 text-sm">
                                                        {assignment.description.substring(0, 150)}
                                                        {assignment.description.length > 150 ? '...' : ''}
                                                    </p>
                                                    <div className="text-muted-foreground mt-2 flex gap-4 text-xs">
                                                        <span>
                                                            Due: {new Date(assignment.due_date).toLocaleDateString()}
                                                        </span>
                                                        <span>{assignment.max_points} points</span>
                                                    </div>
                                                    {submission?.feedback && (
                                                        <div className="bg-muted mt-3 rounded-md p-3">
                                                            <p className="text-xs font-medium">Teacher Feedback:</p>
                                                            <p className="text-muted-foreground mt-1 text-sm">
                                                                {submission.feedback}
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>
                                                <Link href={`/school/assignments/${assignment.id}`}>
                                                    <Button variant="outline" size="sm">
                                                        {submission?.submitted_at ? 'View' : 'Submit'}
                                                    </Button>
                                                </Link>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

