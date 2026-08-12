import Link from 'next/link';

export default function NoResultsFound() {
  return (
    <div className="no-results">
      <div className="no-results-icon">
        <i className="fa-sharp fa-solid fa-magnifying-glass"></i>
      </div>
      <h2 className="no-results-title">No articles found</h2>
      <p className="no-results-desc">
        We couldn't find any articles matching your query. Try different keywords or browse our categories.
      </p>
      <Link href="/category" className="btn btn-primary-custom">
        <span>Browse Categories</span>
        <i className="fa-sharp fa-solid fa-arrow-right"></i>
      </Link>
    </div>
  );
}