'use client';

import CategoriesHero from '@/features/categories/components/CategoriesHero';
import CategoryCard from '@/features/categories/components/CategoryCard';
import CategorySidebar from '@/features/categories/components/CategorySidebar';
import Pagination from '@/shared/components/Pagination';
import { useCategoryList, useCategories } from '@/features/categories/hooks/useCategories';
import { usePopularTags } from '@/features/tags/hooks/usePopularTags';
import '@/styles/category.css';

export default function CategoriesPage() {
  const { search, setSearch, page, setPage, categories, totalPages, isLoading, isError } = useCategoryList();
  const { data: sidebarCategories } = useCategories();
  const { data: popularTags } = usePopularTags();

  const safePopularTags = Array.isArray(popularTags) ? popularTags : [];

  return (
    <>
      <CategoriesHero search={search} onSearchChange={setSearch} />

      <section className="category-posts section-padding">
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
                  <p className="text-muted">Try a different search term.</p>
                </div>
              ) : (
                <>
                  <div className="row g-4" id="categoriesGrid">
                    {categories.map((cat) => (
                      <CategoryCard key={cat.id} category={cat} />
                    ))}
                  </div>
                  <Pagination currentPage={page} lastPage={totalPages} onPageChange={setPage} />
                </>
              )}
            </div>

            {/* Pass showCategories={false} here */}
            <CategorySidebar categories={sidebarCategories || []} popularTags={safePopularTags} showCategories={false} />
          </div>
        </div>
      </section>
    </>
  );
}