'use client';

interface TagsHeroProps {
  search: string;
  onSearchChange: (value: string) => void;
}

export default function TagsHero({ search, onSearchChange }: TagsHeroProps) {
  return (
    <section className="taxonomy-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="taxonomy-hero-content">
          <span className="taxonomy-hero-icon"><i className="fa-sharp fa-solid fa-tags"></i></span>
          <h1 className="taxonomy-hero-title">Browse by <span className="text-gradient">Tag</span></h1>
          <p className="taxonomy-hero-desc">
            Explore all topics and find exactly what you are looking for. Click any tag to see related articles.
          </p>
          <div className="taxonomy-search">
            <i className="fa-sharp fa-solid fa-search search-icon"></i>
            <input 
              type="text" 
              id="tagFilter" 
              className="taxonomy-search-input" 
              placeholder="Filter tags... (e.g. Laravel, React)" 
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