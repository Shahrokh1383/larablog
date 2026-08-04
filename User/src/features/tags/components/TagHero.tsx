interface TagHeroProps {
  tag: {
    name: string;
    posts_count?: number;
  };
}

export default function TagHero({ tag }: TagHeroProps) {
  return (
    <section className="tag-hero">
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container">
        <div className="tag-hero-content">
          <span className="tag-hero-icon"><i className="fa-solid fa-hashtag"></i></span>
          <h1 className="tag-hero-title">{tag.name}</h1>
          <p className="tag-hero-desc">
            Explore the latest articles and tutorials tagged with {tag.name}.
          </p>
          <div className="tag-hero-meta">
            <span><i className="fa-sharp fa-solid fa-file-lines"></i> {tag.posts_count ?? 0} Articles</span>
          </div>
        </div>
      </div>
    </section>
  );
}