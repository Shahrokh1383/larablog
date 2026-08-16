import Link from 'next/link';
import type { Post } from '@/features/posts';

interface CategoryPostCardProps {
  post: Post;
}

export default function CategoryPostCard({ post }: CategoryPostCardProps) {
  return (
    <article className="post-card post-card-standard">
      <div className="post-card-image">
        <img src={post.featured_image || 'https://picsum.photos/seed/catdev1/600/350'} alt={post.title} loading="lazy" />
        {post.category?.name && <span className="post-card-badge">{post.category.name}</span>}
      </div>
      <div className="post-card-body">
        <div className="post-card-meta">
          <span className="post-card-date">{new Date(post.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
          <span className="post-card-read-time">{post.reading_time} min read</span>
        </div>
        <h3 className="post-card-title"><Link href={`/post/${post.slug}`}>{post.title}</Link></h3>
        <p className="post-card-excerpt">{post.excerpt}</p>
        <div className="post-card-footer">
          <div className="post-card-author">
            <img src={post.author?.avatar || 'https://picsum.photos/seed/author1/40/40'} alt={post.author?.name} className="author-avatar" />
            <span className="author-name">{post.author?.name}</span>
          </div>
          <Link href={`/post/${post.slug}`} className="post-card-read-more"><i className="fa-sharp fa-solid fa-arrow-right"></i></Link>
        </div>
      </div>
    </article>
  );
}