import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

interface Submission {
    id: number;
    content: string | null;
    grade: number | null;
    feedback: string | null;
    submitted_at: string | null;
    assignment: {
        id: number;
        title: string;
        max_points: number;
        due_date: string;
    };
}

interface Child {
    id: number;
    name: string;
    email: string;
    submissions: Submission[];
}

interface Props {
    children: Child[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'School',
        href: '/school',
    },
    {
        title: 'Parent Dashboard',
        href: '/school',
    },
];

export default function ParentDashboard({ children }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Parent Dashboard - School" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Your Children's Progress</CardTitle>
                        <CardDescription>Monitor academic performance and submissions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {children.length === 0 ? (
                            <div className="text-muted-foreground py-8 text-center">
                                <p>No children linked to your account.</p>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                {children.map((child) => (
                                    <div key={child.id} className="space-y-4">
                                        <div className="flex items-center gap-3">
                                            <div className="bg-primary text-primary-foreground flex size-12 items-center justify-center rounded-full font-semibold">
                                                {child.name.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <h3 className="font-semibold">{child.name}</h3>
                                                <p className="text-muted-foreground text-sm">{child.email}</p>
                                            </div>
                                        </div>

                                        {child.submissions.length === 0 ? (
                                            <div className="text-muted-foreground ml-15 text-sm">
                                                No submissions yet.
                                            </div>
                                        ) : (
                                            <div className="ml-15 space-y-3">
                                                <h4 className="text-sm font-medium">Recent Submissions</h4>
                                                {child.submissions.map((submission) => (
                                                    <div
                                                        key={submission.id}
                                                        className="rounded-lg border p-4"
                                                    >
                                                        <div className="flex items-start justify-between">
                                                            <div className="flex-1">
                                                                <h5 className="font-medium">
                                                                    {submission.assignment.title}
                                                                </h5>
                                                                <div className="text-muted-foreground mt-1 flex gap-4 text-xs">
                                                                    <span>
                                                                        Due:{' '}
                                                                        {new Date(
                                                                            submission.assignment.due_date
                                                                        ).toLocaleDateString()}
                                                                    </span>
                                                                    {submission.submitted_at && (
                                                                        <span>
                                                                            Submitted:{' '}
                                                                            {new Date(
                                                                                submission.submitted_at
                                                                            ).toLocaleDateString()}
                                                                        </span>
                                                                    )}
                                                                </div>
                                                                {submission.grade !== null ? (
                                                                    <div className="bg-green-50 dark:bg-green-950/20 mt-3 rounded-md p-3">
                                                                        <p className="text-xs font-medium text-green-900 dark:text-green-100">
                                                                            Grade: {submission.grade}/
                                                                            {submission.assignment.max_points}
                                                                        </p>
                                                                        {submission.feedback && (
                                                                            <p className="text-muted-foreground mt-1 text-sm">
                                                                                {submission.feedback}
                                                                            </p>
                                                                        )}
                                                                    </div>
                                                                ) : submission.submitted_at ? (
                                                                    <div className="bg-yellow-50 dark:bg-yellow-950/20 mt-3 rounded-md p-3">
                                                                        <p className="text-xs font-medium text-yellow-900 dark:text-yellow-100">
                                                                            Pending Review
                                                                        </p>
                                                                    </div>
                                                                ) : (
                                                                    <div className="bg-red-50 dark:bg-red-950/20 mt-3 rounded-md p-3">
                                                                        <p className="text-xs font-medium text-red-900 dark:text-red-100">
                                                                            Not Submitted
                                                                        </p>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

