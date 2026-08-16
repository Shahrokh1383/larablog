'use client';

import { TagsHero, TagCard, useTags } from '@/features/tags';
import { usePopularTags } from '@/features/tags';
import { useCategories } from '@/features/categories';
import { useSubscribeNewsletter } from '@/features/newsletter/hooks/useSubscribeNewsletter';
import NewsletterSidebar from '@/features/newsletter/components/NewsletterSidebar';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import Pagination from '@/shared/components/Pagination';
import '@/styles/taxonomy.css';

export default function TagsPage() {
  const { search, setSearch, page, setPage, tags, totalPages, isLoading, isError } = useTags();
  const { data: popularTags = [] } = usePopularTags();
  const { categories: sidebarCategories } = useCategories();
  
  const newsletterState = useSubscribeNewsletter();

  return (
    <>
      <TagsHero search={search} onSearchChange={setSearch} />

      <section className="taxonomy-section section-padding">
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
                <div className="text-center mt-4">
                  <i className="fa-sharp fa-solid fa-circle-exclamation fa-2x text-muted mb-3"></i>
                  <h5>No tags found</h5>
                </div>
              ) : (
                <>
                  <div className="row g-4">
                    {tags.map((tag) => (
                      <div key={tag.id} className="col-md-4 col-6">
                        <TagCard tag={tag} />
                      </div>
                    ))}
                  </div>
                  <Pagination currentPage={page} lastPage={totalPages} onPageChange={setPage} />
                </>
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