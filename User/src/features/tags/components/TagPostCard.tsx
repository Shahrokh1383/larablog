import Link from 'next/link';
import type { Post } from '@/features/posts';

interface TagPostCardProps { post: Post; }

export default function TagPostCard({ post }: TagPostCardProps) {
  return (
    <article className="compact-post-card">
      <div className="compact-post-card__img">
        <img src={post.featured_image || 'https://picsum.photos/seed/tagpost/150/150'} alt={post.title} loading="lazy" />
      </div>
      <div className="compact-post-card__body">
        <div className="compact-post-card__meta">
          {post.category?.name && <span className="compact-post-card__category">{post.category.name}</span>}
          <span className="compact-post-card__read-time">{post.reading_time} min read</span>
        </div>
        <h3 className="compact-post-card__title">
          <Link href={`/post/${post.slug}`}>{post.title}</Link>
        </h3>
        <p className="compact-post-card__excerpt">{post.excerpt?.substring(0, 100)}...</p>
      </div>
    </article>
  );
}