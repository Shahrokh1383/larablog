import Link from 'next/link';
import { Post } from '@/features/posts/types/post';

interface FeaturedPostCardProps {
  post: Post;
  variant: 'large' | 'small';
}

export function FeaturedPostCard({ post, variant }: FeaturedPostCardProps) {
  return (
    <article className={`post-card post-card-featured ${variant === 'large' ? 'post-card-lg' : 'post-card-sm'}`}>
      <div className="post-card-image">
        <img src={post.featured_image} alt={post.title} loading="lazy" />
        <div className="post-card-overlay"></div>
        <span className="post-card-badge">{post.category?.name}</span>
      </div>
      <div className="post-card-body">
        <div className="post-card-meta">
          <span className="post-card-date">
            <i className="fa-sharp fa-solid fa-calendar"></i>
            {post.published_at ? new Date(post.published_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : ''}
          </span>
          <span className="post-card-read-time">
            <i className="fa-sharp fa-solid fa-clock"></i>
            {post.reading_time} min read
          </span>
        </div>
        <h3 className="post-card-title">
          <Link href={`/post/${post.slug}`}>{post.title}</Link>
        </h3>
        {variant === 'large' && (
          <p className="post-card-excerpt">{post.excerpt}</p>
        )}
        <div className="post-card-footer">
          {variant === 'large' && (
            <div className="post-card-author">
              <img src={post.author?.avatar || 'https://picsum.photos/seed/default/40/40'} alt={post.author?.name} className="author-avatar" />
              <span className="author-name">{post.author?.name}</span>
            </div>
          )}
          <Link href={`/post/${post.slug}`} className="post-card-read-more">
            Read more
            <i className="fa-sharp fa-solid fa-arrow-right"></i>
          </Link>
        </div>
      </div>
    </article>
  );
}