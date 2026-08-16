import Link from 'next/link';
import { Author, Category, Post } from '../types/post';
import NewsletterSidebar from '@/features/newsletter/components/NewsletterSidebar';
import { UseMutationResult } from '@tanstack/react-query';

// Define the expected shape of the newsletter mutation state for strict typing
interface NewsletterStateShape {
  mutate: UseMutationResult<any, Error, string>['mutate'];
  isPending: boolean;
  isSuccess: boolean;
  isError: boolean;
}

interface PostSidebarProps {
  author: Author;
  relatedPosts: Post[] | undefined;
  categories: Category[] | undefined;
  newsletterState: NewsletterStateShape; // Passed down from page.tsx
}

export default function PostSidebar({ author, relatedPosts, categories, newsletterState }: PostSidebarProps) {
  // Removed: const newsletterState = useSubscribeNewsletter();
  // Component is now a pure presentation component (Constitution Article V)

  return (
    <aside className="col-lg-4">
      <div className="sidebar">
        {/* Author Card */}
        <div className="sidebar-card author-sidebar-card">
          <div className="text-center">
            <img 
              src={author.avatar || `https://picsum.photos/seed/${author.id}/100/100`} 
              alt={author.name} 
              className="author-sidebar-avatar"
            />
            <h4 className="author-sidebar-name">{author.name}</h4>
            <p className="author-sidebar-bio">{author.bio || 'Full-stack developer, open-source enthusiast, and writer.'}</p>
            <Link href={`/author/${author.username || author.id}`} className="btn btn-outline-custom btn-sm w-100">
              View Profile
            </Link>
          </div>
        </div>

        {/* Related Posts */}
        <div className="sidebar-card">
          <h4 className="sidebar-title">Related Posts</h4>
          <ul className="related-posts-list">
            {relatedPosts?.map((rp) => (
              <li key={rp.id}>
                <Link href={`/post/${rp.slug}`}>{rp.title}</Link>
              </li>
            ))}
            {(!relatedPosts || relatedPosts.length === 0) && (
              <li>No related posts found.</li>
            )}
          </ul>
        </div>

        {/* Categories */}
        <div className="sidebar-card">
          <h4 className="sidebar-title">Categories</h4>
          <div className="category-cloud">
            {categories?.map((cat) => (
              <Link key={cat.id} href={`/category/${cat.slug}`} className="badge-category">
                {cat.name}
              </Link>
            ))}
          </div>
        </div>

        {/* Newsletter */}
        <NewsletterSidebar {...newsletterState} />
      </div>
    </aside>
  );
}