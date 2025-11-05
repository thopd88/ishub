import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Trash2, Edit, Plus } from 'lucide-react';

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

interface PaginatedPosts {
    data: Post[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    posts: PaginatedPosts;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Blog',
        href: '/blog',
    },
];

export default function Index({ posts }: Props) {
    const handleDelete = (postId: number) => {
        if (confirm('Are you sure you want to delete this post?')) {
            router.delete(`/blog/${postId}`, {
                onSuccess: () => {
                    console.log('Post deleted successfully');
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Blog Posts" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">Blog Posts</h1>
                    <Link href="/blog/create">
                        <Button>
                            <Plus className="mr-2 size-4" />
                            New Post
                        </Button>
                    </Link>
                </div>

                <div className="grid gap-4">
                    {posts.data.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center py-12">
                                <p className="text-muted-foreground">No posts found. Create your first post!</p>
                            </CardContent>
                        </Card>
                    ) : (
                        posts.data.map((post) => (
                            <Card key={post.id}>
                                <CardHeader>
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <CardTitle>
                                                <Link
                                                    href={`/blog/${post.id}`}
                                                    className="hover:text-primary transition-colors"
                                                >
                                                    {post.title}
                                                </Link>
                                            </CardTitle>
                                            <CardDescription>
                                                By {post.user.name} •{' '}
                                                {post.published_at
                                                    ? new Date(post.published_at).toLocaleDateString()
                                                    : 'Draft'}
                                            </CardDescription>
                                        </div>
                                        <div className="flex gap-2">
                                            <Link href={`/blog/${post.id}/edit`}>
                                                <Button variant="outline" size="icon">
                                                    <Edit className="size-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                onClick={() => handleDelete(post.id)}
                                            >
                                                <Trash2 className="size-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground line-clamp-3">
                                        {post.content.substring(0, 200)}...
                                    </p>
                                </CardContent>
                            </Card>
                        ))
                    )}
                </div>

                {posts.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {posts.links.map((link, index) => (
                            <Button
                                key={index}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url)}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

