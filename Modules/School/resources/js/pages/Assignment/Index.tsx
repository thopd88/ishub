import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Head, Link, usePage } from '@inertiajs/react';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface Assignment {
    id: number;
    title: string;
    description: string;
    due_date: string;
    max_points: number;
    submissions_count: number;
    teacher: {
        id: number;
        name: string;
    };
}

interface Props {
    assignments: Assignment[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'School',
        href: '/school',
    },
    {
        title: 'Assignments',
        href: '/school/assignments',
    },
];

export default function AssignmentIndex({ assignments }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isTeacher = auth.user.roles?.some((role) => role.name === 'teacher') || false;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assignments - School" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>All Assignments</CardTitle>
                            <CardDescription>View and manage assignments</CardDescription>
                        </div>
                        {isTeacher && (
                            <Link href="/school/assignments/create">
                                <Button>Create Assignment</Button>
                            </Link>
                        )}
                    </CardHeader>
                    <CardContent>
                        {assignments.length === 0 ? (
                            <div className="text-muted-foreground py-8 text-center">
                                <p>No assignments available.</p>
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
                                                        Teacher: {assignment.teacher.name}
                                                    </p>
                                                    <p className="text-muted-foreground mt-2 text-sm">
                                                        {assignment.description.substring(0, 200)}
                                                        {assignment.description.length > 200 ? '...' : ''}
                                                    </p>
                                                    <div className="text-muted-foreground mt-3 flex gap-4 text-xs">
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

