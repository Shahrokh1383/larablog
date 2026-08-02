interface CategoryHeroProps {
  name: string;
  description?: string;
  postsCount: number;
  authorsCount: number;
}

export default function CategoryHero({ name, description, postsCount, authorsCount }: CategoryHeroProps) {
  return (
    <section className="category-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="category-hero-content">
          <span className="category-hero-icon"><i className="fa-sharp fa-solid fa-code"></i></span>
          <h1 className="category-hero-title">{name}</h1>
          <p className="category-hero-desc">{description || `Explore the latest articles, tutorials, and insights on ${name}.`}</p>
          <div className="category-hero-meta">
            <span><i className="fa-sharp fa-solid fa-file-lines"></i> {postsCount} Articles</span>
            <span><i className="fa-sharp fa-solid fa-users"></i> {authorsCount} Authors</span>
          </div>
        </div>
      </div>
    </section>
  );
}