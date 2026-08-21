'use client';

import { useState, useEffect, type ReactNode } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { useDebounce } from '@/shared/hooks/useDebounce';
import { useCategories } from '../hooks/useCategories';
import CategoriesHero from './CategoriesHero';
import CategoryCard from './CategoryCard';
import Pagination from '@/shared/components/Pagination';

export default function CategoriesClientView({
  children,
}: {
  children: ReactNode;
}) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [searchInput, setSearchInput] = useState(searchParams.get('search') || '');
  const debouncedSearch = useDebounce(searchInput, 300);
  const currentPage = parseInt(searchParams.get('page') || '1', 10);

  const { data, isLoading, isError } = useCategories({
    search: debouncedSearch || undefined,
    page: currentPage,
    per_page: 12,
  });

  const categories = data?.data ?? [];
  const totalPages = data?.meta?.last_page ?? 1;

  useEffect(() => {
    const params = new URLSearchParams();
    if (debouncedSearch) {
      params.set('search', debouncedSearch);
    }

    const queryString = params.toString();
    router.push(queryString ? `/category?${queryString}` : '/category', {
      scroll: false,
    });
  }, [debouncedSearch, router]);

  const handlePageChange = (page: number) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('page', page.toString());
    router.push(`/category?${params.toString()}`, { scroll: false });
  };

  return (
    <>
      <CategoriesHero search={searchInput} onSearchChange={setSearchInput} />

      <section className="taxonomy-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="section-header">
                <h2 className="section-title">
                  All <span className="text-gradient">Categories</span>
                </h2>
                <p className="section-desc">
                  Click on a category to discover articles on that topic
                </p>
              </div>

              {isLoading ? (
                <div className="text-center py-5">
                  <div className="spinner-border text-primary"></div>
                </div>
              ) : isError ? (
                <div className="text-center text-danger py-5">
                  Failed to load categories.
                </div>
              ) : categories.length === 0 ? (
                <div className="text-center mt-4">
                  <i className="fa-sharp fa-solid fa-circle-exclamation fa-2x text-muted mb-3"></i>
                  <h5>No categories found</h5>
                </div>
              ) : (
                <>
                  <div className="row g-4" id="categoriesGrid">
                    {categories.map((cat) => (
                      <CategoryCard key={cat.id} category={cat} />
                    ))}
                  </div>
                  <Pagination
                    currentPage={currentPage}
                    lastPage={totalPages}
                    onPageChange={handlePageChange}
                  />
                </>
              )}
            </div>

            <aside className="col-lg-4">{children}</aside>
          </div>
        </div>
      </section>
    </>
  );
}