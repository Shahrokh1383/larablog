'use client';

import AuthorsHero from '@/features/authors/components/AuthorsHero';
import AuthorCard from '@/features/authors/components/AuthorCard';
import NoAuthorsFound from '@/features/authors/components/NoAuthorsFound';
import Pagination from '@/shared/components/Pagination';
import { useAuthors } from '@/features/authors/hooks/useAuthors';
import '@/styles/authors.css';

export default function AuthorsPage() {
  const { search, setSearch, page, setPage, authors, totalPages, isLoading, isError } = useAuthors();

  return (
    <>
      <AuthorsHero search={search} onSearchChange={setSearch} />

      <section className="authors-grid-section section-padding">
        <div className="container">
          <div className="section-header text-center">
            <span className="section-badge">Our Team</span>
            <h2 className="section-title">
              All <span className="text-gradient">Contributors</span>
            </h2>
            <p className="section-desc">Click on an author to view their profile and articles</p>
          </div>

          {isLoading ? (
            <div className="text-center py-5">
              <div className="spinner-border text-primary"></div>
            </div>
          ) : isError ? (
            <div className="text-center text-danger py-5">
              Failed to load authors. Please try again later.
            </div>
          ) : authors.length === 0 ? (
            <NoAuthorsFound />
          ) : (
            <>
              <div className="row g-4" id="authorsGrid">
                {authors.map((author) => (
                  <AuthorCard key={author.id} author={author} />
                ))}
              </div>
              <Pagination currentPage={page} lastPage={totalPages} onPageChange={setPage} />
            </>
          )}
        </div>
      </section>
    </>
  );
}