'use client';

import { useState, use } from 'react';
import { TagHero, TagPostCard, useTagPosts } from '@/features/tags';
import { useCategories } from '@/features/categories';
import { usePopularTags } from '@/features/tags';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { useSubscribeNewsletter } from '@/features/newsletter/hooks/useSubscribeNewsletter';
import NewsletterSidebar from '@/features/newsletter/components/NewsletterSidebar';
import Pagination from '@/shared/components/Pagination';
import '@/styles/taxonomy.css';

export default function TagPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  
  const [sort, setSort] = useState('newest');
  const [page, setPage] = useState(1);

  const { categories: sidebarCategories } = useCategories();
  const { data: popularTags = [] } = usePopularTags();
  const { data, isLoading, isError } = useTagPosts(slug, sort, page);

  const newsletterState = useSubscribeNewsletter();

  if (isLoading) return <div className="container py-5 text-center"><div className="spinner-border text-primary"></div></div>;
  if (isError || !data) return <div className="container py-5 text-center">Error loading tag.</div>;

  return (
    <>
      <TagHero tag={data.tag} />

      <section className="taxonomy-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h2 className="section-title mb-0"><span className="text-gradient">Latest</span> in {data.tag.name}</h2>
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

              <div className="row g-4">
                {data.posts.data.map((post) => (
                  <div key={post.id} className="col-12">
                    <TagPostCard post={post} />
                  </div>
                ))}
              </div>

              {data.posts.meta.last_page > 1 && (
                <Pagination 
                  currentPage={data.posts.meta.current_page} 
                  lastPage={data.posts.meta.last_page} 
                  onPageChange={setPage} 
                />
              )}
            </div>

            <aside className="col-lg-4">
              <BlogSidebar categories={sidebarCategories} popularTags={popularTags} />
              <NewsletterSidebar {...newsletterState} />
            </aside>
          </div>
        </div>
      </section>
    </>
  );
}