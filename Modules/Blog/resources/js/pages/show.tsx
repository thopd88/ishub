import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Trash2, ArrowLeft } from 'lucide-react';

interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    published_at: string | null;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Props {
    post: Post;
}

export default function Show({ post }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Blog',
            href: '/blog',
        },
        {
            title: post.title,
            href: `/blog/${post.id}`,
        },
    ];

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this post?')) {
            router.delete(`/blog/${post.id}`, {
                onSuccess: () => {
                    console.log('Post deleted successfully');
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={post.title} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Link href="/blog">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 size-4" />
                            Back to Posts
                        </Button>
                    </Link>
                    <div className="flex gap-2">
                        <Link href={`/blog/${post.id}/edit`}>
                            <Button variant="outline" size="sm">
                                <Edit className="mr-2 size-4" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="outline" size="sm" onClick={handleDelete}>
                            <Trash2 className="mr-2 size-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <div className="space-y-2">
                            <CardTitle className="text-4xl">{post.title}</CardTitle>
                            <CardDescription className="text-base">
                                By {post.user.name} •{' '}
                                {post.published_at
                                    ? new Date(post.published_at).toLocaleDateString('en-US', {
                                          year: 'numeric',
                                          month: 'long',
                                          day: 'numeric',
                                      })
                                    : 'Draft'}
                            </CardDescription>
                            {!post.published_at && (
                                <div className="inline-flex items-center rounded-md border border-amber-500/50 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/50 dark:text-amber-400">
                                    Draft
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="prose prose-gray max-w-none dark:prose-invert">
                            {post.content.split('\n').map((paragraph, index) => (
                                <p key={index}>{paragraph}</p>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-sm">Post Information</CardTitle>
                    </CardHeader>
                    <CardContent className="text-muted-foreground space-y-2 text-sm">
                        <div className="flex justify-between">
                            <span>Created:</span>
                            <span>
                                {new Date(post.created_at).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span>Last Updated:</span>
                            <span>
                                {new Date(post.updated_at).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span>Slug:</span>
                            <span className="font-mono">{post.slug}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

