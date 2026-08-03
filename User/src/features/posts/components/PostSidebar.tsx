import Link from 'next/link';
import { Author, Category, Post } from '../types/post';

interface PostSidebarProps {
  author: Author;
  relatedPosts: Post[] | undefined;
  categories: Category[] | undefined;
}

export default function PostSidebar({ author, relatedPosts, categories }: PostSidebarProps) {
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
        <div className="sidebar-card newsletter-sidebar">
          <h4 className="sidebar-title">Newsletter</h4>
          <p>Get the best articles delivered to your inbox.</p>
          <form className="newsletter-sidebar-form" onSubmit={(e) => e.preventDefault()}>
            <input type="email" className="form-control" placeholder="your@email.com" required />
            <button type="submit" className="btn btn-primary-custom w-100 mt-2">Subscribe</button>
          </form>
        </div>
      </div>
    </aside>
  );
}