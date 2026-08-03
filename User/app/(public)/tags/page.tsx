'use client';

import TagsHero from '@/features/tags/components/TagsHero';
import TagCard from '@/features/tags/components/TagCard';
import TagsSidebar from '@/features/tags/components/TagsSidebar';
import Pagination from '@/shared/components/Pagination';
import { useTags } from '@/features/tags/hooks/useTags';
import { usePopularTags } from '@/features/tags/hooks/usePopularTags';
import { useCategories } from '@/features/categories/hooks/useCategories';
import '@/styles/tags.css';

export default function TagsPage() {
  const { search, setSearch, page, setPage, tags, totalPages, isLoading, isError } = useTags();
  const { data: popularTags } = usePopularTags();
  const { data: categories } = useCategories(); // Fetch real categories

  const safePopularTags = Array.isArray(popularTags) ? popularTags : [];
  const safeCategories = Array.isArray(categories) ? categories : [];

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
              ) : tags.length === 0 ? (
                <div id="noTagsFound" className="text-center mt-4">
                  <i className="fa-sharp fa-solid fa-circle-exclamation fa-2x text-muted mb-3"></i>
                  <h5>No tags found</h5>
                  <p className="text-muted">Try a different search term.</p>
                </div>
              ) : (
                <>
                  <div className="row g-4" id="tagsGrid">
                    {tags.map((tag) => (
                      <TagCard key={tag.id} tag={tag} />
                    ))}
                  </div>
                  <Pagination currentPage={page} lastPage={totalPages} onPageChange={setPage} />
                </>
              )}
            </div>

            {/* Pass real categories here */}
            <TagsSidebar popularTags={safePopularTags} categories={safeCategories} />
          </div>
        </div>
      </section>
    </>
  );
}