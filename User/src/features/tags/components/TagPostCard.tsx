import Link from 'next/link';

interface TagPostCardProps {
  post: any;
}

export default function TagPostCard({ post }: TagPostCardProps) {
  return (
    <article className="tag-post-card d-flex gap-3 p-3 border rounded-3 mb-3 hover-shadow">
      <div className="tag-post-img flex-shrink-0 d-none d-md-block">
        <img 
          src={post.featured_image || 'https://picsum.photos/seed/tagpost/150/150'} 
          alt={post.title} 
          className="rounded" 
          style={{ width: '120px', height: '120px', objectFit: 'cover' }} 
          loading="lazy" 
        />
      </div>
      <div className="tag-post-body d-flex flex-column">
        <div className="d-flex align-items-center gap-2 mb-1">
          <span className="badge bg-light text-dark border">{post.category?.name}</span>
          <small className="text-muted">{post.reading_time} min read</small>
        </div>
        <h3 className="h6 mb-1">
          <Link href={`/post/${post.slug}`} className="text-decoration-none text-dark stretched-link">
            {post.title}
          </Link>
        </h3>
        <p className="small text-muted mb-0 d-none d-lg-block">
          {post.excerpt?.substring(0, 100)}...
        </p>
      </div>
    </article>
  );
}