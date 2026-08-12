'use client';

import { useSearchParams, useRouter } from 'next/navigation';
import { useSearchResults } from '@/features/search/hooks/useSearchResults';
import SearchHeader from '@/features/search/components/SearchHeader';
import SearchResultsInfo from '@/features/search/components/SearchResultsInfo';
import SearchResultCard from '@/features/search/components/SearchResultCard';
import NoResultsFound from '@/features/search/components/NoResultsFound';
import Pagination from '@/shared/components/Pagination';
import '@/styles/search.css';

export default function SearchPage() {
  const searchParams = useSearchParams();
  const router = useRouter();
  
  const q = searchParams.get('q') || '';
  const page = parseInt(searchParams.get('page') || '1', 10);

  const { data, isLoading, isError } = useSearchResults(q, page);

  const handleSearch = (newQuery: string) => {
    if (newQuery === q) return;
    const params = new URLSearchParams(searchParams.toString());
    params.set('q', newQuery);
    params.set('page', '1');
    router.push(`/search?${params.toString()}`);
  };

  const handlePageChange = (newPage: number) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('page', newPage.toString());
    router.push(`/search?${params.toString()}`);
    
    // Scroll to top of results on page change
    document.querySelector('.search-results-section')?.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <main>
      <section className="search-results-section section-padding">
        <div className="container">
          <SearchHeader query={q} onSearch={handleSearch} />
          
          {isLoading ? (
            <div className="text-center py-5">
              <div className="spinner-border text-primary"></div>
            </div>
          ) : isError ? (
            <div className="text-center py-5 text-danger">Error loading search results.</div>
          ) : data && data.posts.data.length > 0 ? (
            <>
              <SearchResultsInfo query={q} total={data.posts.meta.total} />
              <div className="row g-4">
                {data.posts.data.map(post => (
                  <SearchResultCard key={post.id} post={post} />
                ))}
              </div>
              <Pagination 
                currentPage={data.posts.meta.current_page} 
                lastPage={data.posts.meta.last_page} 
                onPageChange={handlePageChange} 
              />
            </>
          ) : (
            <NoResultsFound />
          )}
        </div>
      </section>
    </main>
  );
}