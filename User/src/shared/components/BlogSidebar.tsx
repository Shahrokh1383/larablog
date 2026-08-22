'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useCategories } from '@/features/categories';
import { usePopularTags } from '@/features/tags';
import { NewsletterSidebar, useSubscribeNewsletter } from '@/features/newsletter';

export function BlogSidebar() {
  const pathname = usePathname();
  const { data: categoryResponse, isLoading: isLoadingCategories } = useCategories({ per_page: 50 });
  const { data: popularTags = [], isLoading: isLoadingTags } = usePopularTags();
  const newsletterState = useSubscribeNewsletter();

  const categories = categoryResponse?.data ?? [];
  const visibleCategories = categories.slice(0, 7);

  const isCategoryIndex = pathname === '/category';
  const isCategorySection = pathname === '/category' || pathname.startsWith('/category/');
  const isTagsSection = pathname === '/tags' || pathname.startsWith('/tags/');
  const showCategories = isCategorySection ? !isCategoryIndex : true;

  const categoriesCard = !showCategories ? null : isLoadingCategories ? (
    <div className="sidebar-card">
      <h4 className="sidebar-title">Categories</h4>
      <div className="text-center py-3">
        <div className="spinner-border spinner-border-sm text-primary"></div>
      </div>
    </div>
  ) : visibleCategories.length > 0 ? (
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
  ) : null;

  const tagsCard = isLoadingTags ? (
    <div className="sidebar-card">
      <h4 className="sidebar-title">Popular Tags</h4>
      <div className="text-center py-3">
        <div className="spinner-border spinner-border-sm text-primary"></div>
      </div>
    </div>
  ) : popularTags.length > 0 ? (
    <div className="sidebar-card">
      <h4 className="sidebar-title">Popular Tags</h4>
      <div className="tag-cloud">
        {popularTags.map((tag) => (
          <Link key={tag.id} href={`/tags/${tag.slug}`} className="tag-badge">
            {tag.name}
          </Link>
        ))}
      </div>
    </div>
  ) : null;

  return (
    <div className="sidebar">
      {isTagsSection ? (
        <>
          {tagsCard}
          {categoriesCard}
        </>
      ) : (
        <>
          {categoriesCard}
          {tagsCard}
        </>
      )}
      <NewsletterSidebar {...newsletterState} />
    </div>
  );
}