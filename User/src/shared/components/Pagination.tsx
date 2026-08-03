interface PaginationProps {
  currentPage: number;
  lastPage: number;
  onPageChange: (page: number) => void;
}

// Helper function to generate smart pagination array (e.g., 1, 2, ..., 5, 6, 7, ..., 20)
const getPaginationItems = (currentPage: number, lastPage: number): (number | string)[] => {
  const items: (number | string)[] = [];
  const delta = 1; // Number of pages to show around the current page

  const range = {
    start: Math.max(2, currentPage - delta),
    end: Math.min(lastPage - 1, currentPage + delta),
  };

  items.push(1);

  if (range.start > 2) {
    items.push('...');
  }

  for (let i = range.start; i <= range.end; i++) {
    items.push(i);
  }

  if (range.end < lastPage - 1) {
    items.push('...');
  }

  if (lastPage > 1) {
    items.push(lastPage);
  }

  return items;
};

export default function Pagination({ currentPage, lastPage, onPageChange }: PaginationProps) {
  if (lastPage <= 1) return null;

  const pages = getPaginationItems(currentPage, lastPage);

  return (
    <nav className="pagination-wrapper mt-5" aria-label="Page navigation">
      <ul className="pagination justify-content-center">
        <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => onPageChange(currentPage - 1)} disabled={currentPage === 1} aria-label="Previous">
            <i className="fa-sharp fa-solid fa-chevron-left"></i>
          </button>
        </li>
        
        {pages.map((page, index) => {
          if (page === '...') {
            return (
              <li key={`ellipsis-${index}`} className="page-item ellipsis" aria-hidden="true">
                <span className="page-link">...</span>
              </li>
            );
          }

          const pageNum = page as number;
          return (
            <li key={pageNum} className={`page-item ${pageNum === currentPage ? 'active' : ''}`}>
              <button className="page-link" onClick={() => onPageChange(pageNum)}>
                {pageNum}
              </button>
            </li>
          );
        })}

        <li className={`page-item ${currentPage === lastPage ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => onPageChange(currentPage + 1)} disabled={currentPage === lastPage} aria-label="Next">
            <i className="fa-sharp fa-solid fa-chevron-right"></i>
          </button>
        </li>
      </ul>
    </nav>
  );
}