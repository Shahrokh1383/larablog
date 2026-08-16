interface CategoryHeroProps {
  name: string;
  description?: string;
  postsCount: number;
  authorsCount: number;
}

export default function CategoryHero({ name, description, postsCount, authorsCount }: CategoryHeroProps) {
  return (
    <section className="taxonomy-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="taxonomy-hero-content">
          <span className="taxonomy-hero-icon"><i className="fa-sharp fa-solid fa-code"></i></span>
          <h1 className="taxonomy-hero-title">{name}</h1>
          <p className="taxonomy-hero-desc">{description || `Explore the latest articles, tutorials, and insights on ${name}.`}</p>
          <div className="taxonomy-hero-meta">
            <span><i className="fa-sharp fa-solid fa-file-lines"></i> {postsCount} Articles</span>
            <span><i className="fa-sharp fa-solid fa-users"></i> {authorsCount} Authors</span>
          </div>
        </div>
      </div>
    </section>
  );
}