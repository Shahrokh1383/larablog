import Link from 'next/link';
import type { Tag } from '../types/tag';
import type { Category } from '@/features/categories/types/category';

interface TagsSidebarProps {
  popularTags: Tag[];
  categories: Category[];
}

export default function TagsSidebar({ popularTags, categories }: TagsSidebarProps) {
  // Limit to 7 items for the sidebar
  const visibleCategories = categories.slice(0, 7);

  return (
    <aside className="col-lg-4">
      <div className="sidebar">
        <div className="sidebar-card">
          <h4 className="sidebar-title">Popular Tags</h4>
          <div className="popular-tags-cloud">
            {popularTags.map((tag, index) => (
              <Link 
                key={tag.id} 
                href={`/tags/${tag.slug}`}
                className="popular-tag-badge"
                style={{ fontSize: `${0.9 + (index % 4) * 0.15}rem` }}
              >
                {tag.name}
              </Link>
            ))}
          </div>
        </div>

        <div className="sidebar-card">
          <h4 className="sidebar-title">Categories</h4>
          <ul className="categories-sidebar-list">
            {visibleCategories.map((cat) => (
              <li key={cat.id}>
                <Link href={`/category/${cat.slug}`}>
                  <i className="fa-sharp fa-solid fa-folder"></i> {cat.name} <span>{cat.posts_count}</span>
                </Link>
              </li>
            ))}
          </ul>
          <Link href="/category" className="btn btn-outline-primary btn-sm w-100 mt-3">
            View All Categories
          </Link>
        </div>

        <div className="sidebar-card newsletter-sidebar">
          <h4 className="sidebar-title">Newsletter</h4>
          <p>Get the best articles delivered to your inbox.</p>
          <form className="newsletter-sidebar-form">
            <input type="email" className="form-control" placeholder="your@email.com" required />
            <button type="submit" className="btn btn-primary-custom w-100 mt-2">Subscribe</button>
          </form>
        </div>
      </div>
    </aside>
  );
}