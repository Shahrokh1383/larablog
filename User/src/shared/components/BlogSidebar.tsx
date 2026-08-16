import Link from 'next/link';
import type { Category } from '@/features/categories';
import type { Tag } from '@/features/tags';

interface BlogSidebarProps {
  categories: Category[];
  popularTags: Tag[];
}

export function BlogSidebar({ categories, popularTags }: BlogSidebarProps) {
  const visibleCategories = categories.slice(0, 7);

  return (
    <div className="sidebar">
      {visibleCategories.length > 0 && (
        <div className="sidebar-card">
          <h4 className="sidebar-title">Categories</h4>
          <ul className="taxonomy-sidebar-list">
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

      {popularTags.length > 0 && (
        <div className="sidebar-card">
          <h4 className="sidebar-title">Popular Tags</h4>
          <div className="tag-cloud">
            {popularTags.map((tag) => (
              <Link key={tag.id} href={`/tags/${tag.slug}`} className="tag-badge">{tag.name}</Link>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}