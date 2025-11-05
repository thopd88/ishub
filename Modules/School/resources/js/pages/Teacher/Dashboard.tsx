import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

interface Assignment {
    id: number;
    title: string;
    description: string;
    due_date: string;
    max_points: number;
    submissions_count: number;
    created_at: string;
}

interface Stats {
    total_assignments: number;
    total_submissions: number;
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
        title: 'Teacher Dashboard',
        href: '/school',
    },
];

export default function TeacherDashboard({ assignments, stats }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Teacher Dashboard - School" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                {/* Stats Section */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardDescription>Total Assignments</CardDescription>
                            <CardTitle className="text-4xl">{stats.total_assignments}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardDescription>Total Submissions</CardDescription>
                            <CardTitle className="text-4xl">{stats.total_submissions}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardDescription>Average Submissions</CardDescription>
                            <CardTitle className="text-4xl">
                                {stats.total_assignments > 0
                                    ? Math.round(stats.total_submissions / stats.total_assignments)
                                    : 0}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                {/* Assignments Section */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Your Assignments</CardTitle>
                            <CardDescription>Manage and grade student submissions</CardDescription>
                        </div>
                        <Link href="/school/assignments/create">
                            <Button>Create Assignment</Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        {assignments.length === 0 ? (
                            <div className="text-muted-foreground py-8 text-center">
                                <p>No assignments yet. Create your first assignment to get started!</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {assignments.map((assignment) => (
                                    <Link
                                        key={assignment.id}
                                        href={`/school/assignments/${assignment.id}`}
                                        className="block"
                                    >
                                        <div className="hover:bg-muted/50 rounded-lg border p-4 transition-colors">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <h3 className="font-semibold">{assignment.title}</h3>
                                                    <p className="text-muted-foreground mt-1 text-sm">
                                                        {assignment.description.substring(0, 150)}
                                                        {assignment.description.length > 150 ? '...' : ''}
                                                    </p>
                                                    <div className="text-muted-foreground mt-2 flex gap-4 text-xs">
                                                        <span>
                                                            Due: {new Date(assignment.due_date).toLocaleDateString()}
                                                        </span>
                                                        <span>{assignment.max_points} points</span>
                                                        <span>{assignment.submissions_count} submissions</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

