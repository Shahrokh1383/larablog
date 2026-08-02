'use client';

import { useState, useEffect } from 'react';
import { useSearchParams } from 'next/navigation';
import TagsHero from '@/features/tags/components/TagsHero';
import TagCard from '@/features/tags/components/TagCard';
import TagsSidebar from '@/features/tags/components/TagsSidebar';
import { useTags } from '@/features/tags/hooks/useTags';
import { usePopularTags } from '@/features/tags/hooks/usePopularTags';

export default function TagsPage() {
  const searchParams = useSearchParams();
  const [search, setSearch] = useState(searchParams.get('search') || '');
  const [page, setPage] = useState(1);

  // Debounce search input for better UX and API performance
  const [debouncedSearch, setDebouncedSearch] = useState(search);
  useEffect(() => {
    const handler = setTimeout(() => setDebouncedSearch(search), 300);
    return () => clearTimeout(handler);
  }, [search]);

  const { data, isLoading, isError } = useTags(debouncedSearch, page);
  const { data: popularTags } = usePopularTags();

  return (
    <>
      <TagsHero search={search} onSearchChange={setSearch} />

      <section className="tags-grid-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="section-header">
                <h2 className="section-title">All <span className="text-gradient">Tags</span></h2>
                <p className="section-desc">Click on a tag to discover articles on that topic</p>
              </div>

              {isLoading ? (
                <div className="text-center py-5"><div className="spinner-border text-primary"></div></div>
              ) : isError ? (
                <div className="text-center text-danger py-5">Failed to load tags.</div>
              ) : data?.data?.length === 0 ? (
                <div id="noTagsFound" className="text-center mt-4">
                  <i className="fa-sharp fa-solid fa-circle-exclamation fa-2x text-muted mb-3"></i>
                  <h5>No tags found</h5>
                  <p className="text-muted">Try a different search term.</p>
                </div>
              ) : (
                <div className="row g-4" id="tagsGrid">
                  {data?.data.map((tag: any) => (
                    <TagCard key={tag.id} tag={tag} />
                  ))}
                </div>
              )}

              {/* Pagination UI */}
              {data?.meta?.last_page > 1 && (
                <nav className="pagination-wrapper mt-5" aria-label="Page navigation">
                  <ul className="pagination justify-content-center">
                    <li className={`page-item ${page === 1 ? 'disabled' : ''}`}>
                      <button className="page-link" onClick={() => setPage(page - 1)} disabled={page === 1}>
                        <i className="fa-sharp fa-solid fa-chevron-left"></i>
                      </button>
                    </li>
                    {Array.from({ length: data.meta.last_page }, (_, i) => i + 1).map(p => (
                      <li key={p} className={`page-item ${p === page ? 'active' : ''}`}>
                        <button className="page-link" onClick={() => setPage(p)}>{p}</button>
                      </li>
                    ))}
                    <li className={`page-item ${page === data.meta.last_page ? 'disabled' : ''}`}>
                      <button className="page-link" onClick={() => setPage(page + 1)} disabled={page === data.meta.last_page}>
                        <i className="fa-sharp fa-solid fa-chevron-right"></i>
                      </button>
                    </li>
                  </ul>
                </nav>
              )}
            </div>

            <TagsSidebar popularTags={popularTags || []} />
          </div>
        </div>
      </section>
    </>
  );
}