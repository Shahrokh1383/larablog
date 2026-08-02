'use client';

import Link from 'next/link';

interface CategorySidebarProps {
  categories: any[];
  popularTags: any[];
}

export default function CategorySidebar({ categories, popularTags }: CategorySidebarProps) {
  return (
    <aside className="col-lg-4">
      <div className="sidebar">
        <div className="sidebar-card">
          <h4 className="sidebar-title">All Categories</h4>
          <ul className="categories-sidebar-list">
            {categories.map((cat) => (
              <li key={cat.id}>
                <Link href={`/category/${cat.slug}`} className={cat.active ? 'active' : ''}>
                  <i className="fa-sharp fa-solid fa-code"></i> {cat.name} <span>{cat.posts_count}</span>
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <div className="sidebar-card">
          <h4 className="sidebar-title">Popular Tags</h4>
          <div className="tag-cloud">
            {popularTags.map((tag) => (
              <Link key={tag.id} href={`/tags?search=${tag.slug}`} className="tag-badge">{tag.name}</Link>
            ))}
          </div>
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