import Link from 'next/link';

interface TagPostCardProps {
  post: any;
}

export default function TagPostCard({ post }: TagPostCardProps) {
  return (
    <article className="tag-post-card">
      <div className="tag-post-img">
        <img 
          src={post.featured_image || 'https://picsum.photos/seed/tagpost/150/150'} 
          alt={post.title} 
          loading="lazy" 
        />
      </div>
      <div className="tag-post-body">
        <div className="tag-post-meta">
          {post.category?.name && <span className="tag-post-category">{post.category.name}</span>}
          <span className="tag-post-read-time">{post.reading_time} min read</span>
        </div>
        <h3 className="tag-post-title">
          <Link href={`/post/${post.slug}`}>
            {post.title}
          </Link>
        </h3>
        <p className="tag-post-excerpt">
          {post.excerpt?.substring(0, 100)}...
        </p>
      </div>
    </article>
  );
}