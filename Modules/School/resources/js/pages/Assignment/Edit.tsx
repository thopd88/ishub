import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Head, useForm } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';
import { FormEventHandler } from 'react';

interface Assignment {
    id: number;
    title: string;
    description: string;
    due_date: string;
    max_points: number;
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
    {
        title: 'Edit',
        href: `/school/assignments/${assignment.id}/edit`,
    },
];

export default function EditAssignment({ assignment }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        title: assignment.title,
        description: assignment.description,
        due_date: assignment.due_date.replace(' ', 'T').substring(0, 16),
        max_points: assignment.max_points,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/school/assignments/${assignment.id}`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs(assignment)}>
            <Head title={`Edit ${assignment.title} - School`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Card className="mx-auto w-full max-w-2xl">
                    <CardHeader>
                        <CardTitle>Edit Assignment</CardTitle>
                        <CardDescription>Update assignment details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    required
                                />
                                {errors.title && (
                                    <p className="text-sm text-red-600 dark:text-red-400">{errors.title}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    required
                                    rows={6}
                                />
                                {errors.description && (
                                    <p className="text-sm text-red-600 dark:text-red-400">{errors.description}</p>
                                )}
                            </div>

                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="due_date">Due Date</Label>
                                    <Input
                                        id="due_date"
                                        type="datetime-local"
                                        value={data.due_date}
                                        onChange={(e) => setData('due_date', e.target.value)}
                                        required
                                    />
                                    {errors.due_date && (
                                        <p className="text-sm text-red-600 dark:text-red-400">{errors.due_date}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="max_points">Maximum Points</Label>
                                    <Input
                                        id="max_points"
                                        type="number"
                                        value={data.max_points}
                                        onChange={(e) => setData('max_points', parseInt(e.target.value))}
                                        required
                                        min="1"
                                        max="1000"
                                    />
                                    {errors.max_points && (
                                        <p className="text-sm text-red-600 dark:text-red-400">{errors.max_points}</p>
                                    )}
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Updating...' : 'Update Assignment'}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

