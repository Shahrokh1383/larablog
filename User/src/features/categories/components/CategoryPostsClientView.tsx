'use client';

import { useState, useEffect } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { useCategoryPosts } from '../hooks/useCategoryPosts';
import CategoryHero from './CategoryHero';
import CategoryPostCard from './CategoryPostCard';
import Pagination from '@/shared/components/Pagination';

interface CategoryPostsClientViewProps {
  slug: string;
  categoryData: {
    name: string;
    posts_count: number;
    authors_count: number;
  };
}

export default function CategoryPostsClientView({ slug, categoryData }: CategoryPostsClientViewProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  
  const [sort, setSort] = useState(searchParams.get('sort') || 'newest');
  const currentPage = parseInt(searchParams.get('page') || '1', 10);

  const { data, isLoading, isError } = useCategoryPosts(slug, sort, currentPage);

  // Sync URL with sort and page
  useEffect(() => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('sort', sort);
    params.set('page', currentPage.toString());
    router.push(`/category/${slug}?${params.toString()}`, { scroll: false });
  }, [sort, currentPage, router, searchParams, slug]);

  const handleSortChange = (newSort: string) => {
    setSort(newSort);
    const params = new URLSearchParams(searchParams.toString());
    params.set('page', '1'); // Reset to page 1 on sort change
    router.push(`/category/${slug}?${params.toString()}`, { scroll: false });
  };

  const handlePageChange = (page: number) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('page', page.toString());
    router.push(`/category/${slug}?${params.toString()}`, { scroll: false });
  };

  if (isLoading) return <div className="container py-5 text-center"><div className="spinner-border text-primary"></div></div>;
  if (isError || !data) return <div className="container py-5 text-center">Error loading category.</div>;

  return (
    <>
      <CategoryHero 
        name={categoryData.name}
        postsCount={categoryData.posts_count}
        authorsCount={categoryData.authors_count}
      />

      <section className="taxonomy-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h2 className="section-title mb-0"><span className="text-gradient">Latest</span> in {categoryData.name}</h2>
                <div className="sort-dropdown">
                  <select 
                    className="form-select sort-select" 
                    value={sort}
                    onChange={(e) => handleSortChange(e.target.value)}
                  >
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="most_popular">Most Popular</option>
                  </select>
                </div>
              </div>

              <div className="row g-4">
                {data.posts.data.map((post) => (
                  <div key={post.id} className="col-md-6">
                    <CategoryPostCard post={post} />
                  </div>
                ))}
              </div>

              <Pagination 
                currentPage={data.posts.meta.current_page} 
                lastPage={data.posts.meta.last_page} 
                onPageChange={handlePageChange} 
              />
            </div>
          </div>
        </div>
      </section>
    </>
  );
}