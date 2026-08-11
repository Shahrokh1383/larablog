import { Post } from '@/features/posts/types/post';
import { FeaturedPostCard } from './FeaturedPostCard';

interface FeaturedPostsProps {
  posts: Post[];
}

export function FeaturedPosts({ posts }: FeaturedPostsProps) {
  if (posts.length === 0) return null;

  const hasLarge = posts.length === 3 || posts.length === 4;
  const largePost = hasLarge ? posts[0] : null;
  const smallPosts = hasLarge ? posts.slice(1) : posts;

  return (
    <div className="row g-4 featured-grid">
      {largePost && (
        <div className="col-lg-8">
          <FeaturedPostCard post={largePost} variant="large" />
        </div>
      )}
      {smallPosts.length > 0 && (
        <div className={`col-lg-4 ${!largePost ? 'col-lg-12' : ''}`}>
          <div className="d-flex flex-column gap-4 h-100">
            {smallPosts.map(post => (
              <div key={post.id} className="flex-grow-1">
                <FeaturedPostCard post={post} variant="small" />
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}