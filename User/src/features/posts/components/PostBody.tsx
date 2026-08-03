import { Post } from '../types/post';

interface PostBodyProps {
  post: Post;
}

export default function PostBody({ post }: PostBodyProps) {
  return (
    <div className="post-body" dangerouslySetInnerHTML={{ __html: post.body }} />
  );
}