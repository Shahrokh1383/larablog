import Link from 'next/link';
import type { Post } from '@/features/posts/types/post';

interface AuthorPostCardProps {
  post: Post;
}

export default function AuthorPostCard({ post }: AuthorPostCardProps) {
  return (
    <div className="col-md-6">
      <article className="post-card post-card-standard">
        <div className="post-card-image">
          <img 
            src={post.featured_image || `https://picsum.photos/seed/${post.id}/600/350`} 
            alt={post.title} 
            loading="lazy" 
          />
          {post.category && <span className="post-card-badge">{post.category.name}</span>}
        </div>
        <div className="post-card-body">
          <div className="post-card-meta">
            <span className="post-card-date">
              {new Date(post.published_at || post.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
            </span>
            <span className="post-card-read-time">{post.reading_time} min read</span>
          </div>
          <h3 className="post-card-title">
            <Link href={`/post/${post.slug}`}>{post.title}</Link>
          </h3>
          <p className="post-card-excerpt">{post.excerpt}</p>
          <div className="post-card-footer">
            <Link href={`/post/${post.slug}`} className="post-card-read-more">
              Read more <i className="fa-sharp fa-solid fa-arrow-right"></i>
            </Link>
          </div>
        </div>
      </article>
    </div>
  );
}