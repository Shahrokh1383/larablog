interface AuthorsHeroProps {
  search: string;
  onSearchChange: (value: string) => void;
}

export default function AuthorsHero({ search, onSearchChange }: AuthorsHeroProps) {
  return (
    <section className="authors-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="authors-hero-content">
          <span className="authors-hero-icon">
            <i className="fa-sharp fa-solid fa-users"></i>
          </span>
          <h1 className="authors-hero-title">
            Our <span className="text-gradient">Authors</span>
          </h1>
          <p className="authors-hero-desc">
            Meet the talented writers who bring you insightful articles, tutorials, and stories every week.
          </p>
          <div className="author-search-wrapper">
            <i className="fa-sharp fa-solid fa-search search-icon"></i>
            <input
              type="text"
              className="author-search-input"
              placeholder="Search authors by name or role..."
              autoComplete="off"
              value={search}
              onChange={(e) => onSearchChange(e.target.value)}
            />
          </div>
        </div>
      </div>
    </section>
  );
}