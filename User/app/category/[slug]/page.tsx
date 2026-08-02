'use client';

import { useState } from 'react';
import { useParams, useSearchParams } from 'next/navigation';
import Link from 'next/link';
import CategoryHero from '@/features/categories/components/CategoryHero';
import CategoryPostCard from '@/features/categories/components/CategoryPostCard';
import { useCategoryPosts } from '@/features/categories/hooks/useCategoryPosts';
import { useCategories } from '@/features/categories/hooks/useCategories';
import { usePopularTags } from '@/features/tags/hooks/usePopularTags';
import CategorySidebar from '@/features/categories/components/CategorySidebar';

export default function CategoryPage() {
  const params = useParams();
  const searchParams = useSearchParams();
  const slug = params.slug as string;
  
  const [sort, setSort] = useState(searchParams.get('sort') || 'newest');
  const [page, setPage] = useState(1);

  // Fetch sidebar data
  const { data: categories } = useCategories();
  const { data: popularTags } = usePopularTags();

  // Fetch main content
  const { data, isLoading, isError } = useCategoryPosts(slug, sort, page);

  if (isLoading) return <div className="container py-5 text-center"><div className="spinner-border text-primary"></div></div>;
  if (isError || !data) return <div className="container py-5 text-center">Error loading category.</div>;

  return (
    <>
      <CategoryHero 
        name={data.category.name} 
        description={data.category.description}
        postsCount={data.category.posts_count}
        authorsCount={data.category.authors_count}
      />

      <section className="category-posts section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h2 className="section-title mb-0"><span className="text-gradient">Latest</span> in {data.category.name}</h2>
                <div className="sort-dropdown">
                  <select 
                    className="form-select sort-select" 
                    value={sort}
                    onChange={(e) => { setSort(e.target.value); setPage(1); }}
                  >
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="most_popular">Most Popular</option>
                  </select>
                </div>
              </div>

              <div className="row g-4" id="postsGrid">
                {data.posts.data.map((post: any) => (
                  <CategoryPostCard key={post.id} post={post} />
                ))}
              </div>

              {/* Pagination UI */}
              <nav className="pagination-wrapper mt-5" aria-label="Page navigation">
                <ul className="pagination justify-content-center">
                  <li className={`page-item ${!data.posts.prev_page_url ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => setPage(page - 1)} disabled={!data.posts.prev_page_url}>
                      <i className="fa-sharp fa-solid fa-chevron-left"></i>
                    </button>
                  </li>
                  {Array.from({ length: data.posts.last_page }, (_, i) => i + 1).map(p => (
                    <li key={p} className={`page-item ${p === page ? 'active' : ''}`}>
                      <button className="page-link" onClick={() => setPage(p)}>{p}</button>
                    </li>
                  ))}
                  <li className={`page-item ${!data.posts.next_page_url ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => setPage(page + 1)} disabled={!data.posts.next_page_url}>
                      <i className="fa-sharp fa-solid fa-chevron-right"></i>
                    </button>
                  </li>
                </ul>
              </nav>
            </div>

            <CategorySidebar categories={categories || []} popularTags={popularTags || []} />
          </div>
        </div>
      </section>
    </>
  );
}