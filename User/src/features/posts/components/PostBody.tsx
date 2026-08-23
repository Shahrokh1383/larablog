import { Post } from '../types/post';
import { sanitizeHtml } from '@/shared/lib/sanitizeHtml';

interface PostBodyProps {
  post: Post;
}

export default function PostBody({ post }: PostBodyProps) {
  const safeHtml = sanitizeHtml(post.body);
  
  return (
    <div 
      className="post-body" 
      dangerouslySetInnerHTML={{ __html: safeHtml }} 
    />
  );
}