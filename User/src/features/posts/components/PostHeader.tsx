import Link from 'next/link';
import { ReactNode } from 'react';
import { Post } from '../types/post';

interface PostHeaderProps {
  post: Post;
  action?: ReactNode;
}

export default function PostHeader({ post, action }: PostHeaderProps) {
  const formattedDate = new Date(post.created_at).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });

  return (
    <header className="post-header">
      <span className="post-badge">{post.category.name}</span>
      <h1 className="post-title">{post.title}</h1>
      <div className="post-meta">
        <div className="post-meta-author">
          <img 
            src={post.author.avatar || `https://picsum.photos/seed/${post.author.id}/40/40`} 
            alt={post.author.name} 
            className="author-avatar"
          />
          <div>
            <Link href={`/author/${post.author.username || post.author.id}`} className="author-name">
              {post.author.name}
            </Link>
            <span className="post-date">{formattedDate}</span>
          </div>
        </div>
        <div className="post-meta-actions">
          <div className="post-meta-details">
            <span><i className="fa-sharp fa-solid fa-clock"></i> {post.reading_time} min read</span>
            <span><i className="fa-sharp fa-solid fa-eye"></i> {post.views} Views</span>
            <span><i className="fa-sharp fa-solid fa-comment"></i> {post.comments_count ?? 0} Comments</span>
          </div>
          {action}
        </div>
      </div>
    </header>
  );
}