interface SearchResultsInfoProps {
  query: string;
  total: number;
}

export default function SearchResultsInfo({ query, total }: SearchResultsInfoProps) {
  return (
    <div className="results-info">
      Showing <strong>{total}</strong> result{total !== 1 ? 's' : ''} for "<strong>{query}</strong>"
    </div>
  );
}