interface CategoriesHeroProps {
  search: string;
  onSearchChange: (value: string) => void;
}

export default function CategoriesHero({ search, onSearchChange }: CategoriesHeroProps) {
  return (
    <section className="category-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="category-hero-content">
          <span className="category-hero-icon"><i className="fa-sharp fa-solid fa-layer-group"></i></span>
          <h1 className="category-hero-title">Browse by <span className="text-gradient">Category</span></h1>
          <p className="category-hero-desc">
            Explore all categories and find exactly what you're looking for. Click any category to see related articles.
          </p>
          <div className="tag-search-wrapper">
            <i className="fa-sharp fa-solid fa-search search-icon"></i>
            <input 
              type="text" 
              id="categoryFilter" 
              className="tag-search-input" 
              placeholder="Filter categories... (e.g. Development, Design)" 
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