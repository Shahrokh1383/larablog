'use client';

import { type ReactNode } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { useTagPosts } from '../hooks/useTagPosts';
import TagHero from './TagHero';
import TagPostCard from './TagPostCard';
import Pagination from '@/shared/components/Pagination';
import type { Tag } from '../types/tag';

interface TagPostsClientViewProps {
  slug: string;
  tagData: Tag;
  children: ReactNode;
}

export default function TagPostsClientView({
  slug,
  tagData,
  children,
}: TagPostsClientViewProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const sort = searchParams.get('sort') || 'newest';
  const currentPage = parseInt(searchParams.get('page') || '1', 10);

  const { data, isLoading, isError } = useTagPosts(slug, sort, currentPage, 9);

  const handleSortChange = (newSort: string) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('sort', newSort);
    params.set('page', '1');
    router.push(`/tags/${slug}?${params.toString()}`, { scroll: false });
  };

  const handlePageChange = (page: number) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('sort', sort);
    params.set('page', page.toString());
    router.push(`/tags/${slug}?${params.toString()}`, { scroll: false });
  };

  if (isLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary"></div>
      </div>
    );
  }

  if (isError || !data) {
    return <div className="text-center text-danger py-5">Error loading tag.</div>;
  }

  return (
    <>
      <TagHero tag={tagData} />

      <section className="taxonomy-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h2 className="section-title mb-0">
                  <span className="text-gradient">Latest</span> in {tagData.name}
                </h2>
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
                  <div key={post.id} className="col-12">
                    <TagPostCard post={post} />
                  </div>
                ))}
              </div>

              {data.posts.meta.last_page > 1 && (
                <Pagination
                  currentPage={data.posts.meta.current_page}
                  lastPage={data.posts.meta.last_page}
                  onPageChange={handlePageChange}
                />
              )}
            </div>

            <aside className="col-lg-4">{children}</aside>
          </div>
        </div>
      </section>
    </>
  );
}