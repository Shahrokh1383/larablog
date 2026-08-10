'use client';

import Link from 'next/link';
import type { Category } from '../types/category';
import type { Tag } from '@/features/tags/types/tag';
import NewsletterSidebar from '@/features/newsletter/components/NewsletterSidebar';
import { useSubscribeNewsletter } from '@/features/newsletter/hooks/useSubscribeNewsletter';

interface CategorySidebarProps {
  categories: Category[];
  popularTags: Tag[];
  showCategories?: boolean; 
}

export default function CategorySidebar({ categories, popularTags, showCategories = true }: CategorySidebarProps) {
  const visibleCategories = categories.slice(0, 7);
  const newsletterState = useSubscribeNewsletter();

  return (
    <aside className="col-lg-4">
      <div className="sidebar">
        {showCategories && (
          <div className="sidebar-card">
            <h4 className="sidebar-title">All Categories</h4>
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
        )}

        <div className="sidebar-card">
          <h4 className="sidebar-title">Popular Tags</h4>
          <div className="tag-cloud">
            {popularTags.map((tag) => (
              <Link key={tag.id} href={`/tags/${tag.slug}`} className="tag-badge">{tag.name}</Link>
            ))}
          </div>
        </div>

        {/* Newsletter (DRY Refactor) */}
        <NewsletterSidebar {...newsletterState} />
      </div>
    </aside>
  );
}