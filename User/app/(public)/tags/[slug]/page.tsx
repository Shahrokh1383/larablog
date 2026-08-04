'use client';

import { useState } from 'react';
import { useTagPosts } from '@/features/tags/hooks/useTagPosts';
import TagHero from '@/features/tags/components/TagHero';
import TagPostCard from '@/features/tags/components/TagPostCard';
import Pagination from '@/shared/components/Pagination';

export default function TagPage({ params }: { params: { slug: string } }) {
  const [sort, setSort] = useState('newest');
  const [page, setPage] = useState(1);

  const { data, isLoading, isError } = useTagPosts(params.slug, sort, page);

  if (isLoading) return <div className="text-center py-5">Loading posts...</div>;
  if (isError || !data) return <div className="text-center py-5">Error loading posts.</div>;

  return (
    <div className="container py-4">
      <TagHero tag={data.tag} />
      
      <div className="row justify-content-center">
        <div className="col-lg-8">
          <div className="d-flex justify-content-between align-items-center mb-4">
            <span className="text-muted">Showing {data.posts.meta.total} results</span>
            <select 
              className="form-select form-select-sm w-auto" 
              value={sort} 
              onChange={(e) => { setSort(e.target.value); setPage(1); }}
            >
              <option value="newest">Newest</option>
              <option value="oldest">Oldest</option>
              <option value="most_popular">Most Popular</option>
            </select>
          </div>

          {data.posts.data.length > 0 ? (
            data.posts.data.map((post: any) => (
              <TagPostCard key={post.id} post={post} />
            ))
          ) : (
            <p className="text-center py-5 text-muted">No posts found for this tag.</p>
          )}

          {data.posts.meta.last_page > 1 && (
            <Pagination 
              currentPage={data.posts.meta.current_page}
              lastPage={data.posts.meta.last_page}
              onPageChange={(newPage) => setPage(newPage)}
            />
          )}
        </div>
      </div>
    </div>
  );
}