'use client';

import { useState, use } from 'react';
import { useTagPosts } from '@/features/tags/hooks/useTagPosts';
import { useCategories } from '@/features/categories/hooks/useCategories';
import { usePopularTags } from '@/features/tags/hooks/usePopularTags';
import TagHero from '@/features/tags/components/TagHero';
import TagPostCard from '@/features/tags/components/TagPostCard';
import TagsSidebar from '@/features/tags/components/TagsSidebar';
import Pagination from '@/shared/components/Pagination';
import '@/styles/tags.css';

export default function TagPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  
  const [sort, setSort] = useState('newest');
  const [page, setPage] = useState(1);

  const { data: categories } = useCategories();
  const { data: popularTags } = usePopularTags();

  const { data, isLoading, isError } = useTagPosts(slug, sort, page);

  if (isLoading) return <div className="container py-5 text-center"><div className="spinner-border text-primary"></div></div>;
  if (isError || !data) return <div className="container py-5 text-center">Error loading tag.</div>;

  return (
    <>
      <TagHero tag={data.tag} />

      <section className="tags-grid-section section-padding">
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

              {/* Using a row g-4 to match the category grid structure, though TagPostCard will take full width */}
              <div className="row g-4" id="postsGrid">
                {data.posts.data.map((post: any) => (
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

            <TagsSidebar popularTags={popularTags || []} categories={categories || []} />
          </div>
        </div>
      </section>
    </>
  );
}