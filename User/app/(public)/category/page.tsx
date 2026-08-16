'use client';

import { CategoriesHero, CategoryCard, useCategoryList, useCategories } from '@/features/categories';
import { usePopularTags } from '@/features/tags';
import { useSubscribeNewsletter } from '@/features/newsletter/hooks/useSubscribeNewsletter';
import NewsletterSidebar from '@/features/newsletter/components/NewsletterSidebar';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import Pagination from '@/shared/components/Pagination';
import '@/styles/taxonomy.css';

export default function CategoriesPage() {
  const { search, setSearch, page, setPage, categories, totalPages, isLoading, isError } = useCategoryList();
  const { categories: sidebarCategories } = useCategories();
  const { data: popularTags = [] } = usePopularTags();
  
  // Hook lifted to orchestrator to obey Article V
  const newsletterState = useSubscribeNewsletter();

  return (
    <>
      <CategoriesHero search={search} onSearchChange={setSearch} />

      <section className="taxonomy-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="section-header">
                <h2 className="section-title">All <span className="text-gradient">Categories</span></h2>
                <p className="section-desc">Click on a category to discover articles on that topic</p>
              </div>

              {isLoading ? (
                <div className="text-center py-5"><div className="spinner-border text-primary"></div></div>
              ) : isError ? (
                <div className="text-center text-danger py-5">Failed to load categories.</div>
              ) : categories.length === 0 ? (
                <div className="text-center mt-4">
                  <i className="fa-sharp fa-solid fa-circle-exclamation fa-2x text-muted mb-3"></i>
                  <h5>No categories found</h5>
                </div>
              ) : (
                <>
                  <div className="row g-4">
                    {categories.map((cat) => (
                      <div key={cat.id} className="col-md-4 col-6">
                        <CategoryCard category={cat} />
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