interface TagHeroProps {
  tag: {
    name: string;
    posts_count?: number;
  };
}

export default function TagHero({ tag }: TagHeroProps) {
  return (
    <div className="tag-hero text-center py-5 mb-5 border-bottom">
      <span className="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3 fs-6">
        <i className="fa-solid fa-tag me-1"></i> Tag
      </span>
      <h1 className="display-5 fw-bold text-dark mb-2">{tag.name}</h1>
      <p className="text-muted mb-0">
        {tag.posts_count ?? 0} Posts tagged with this topic
      </p>
    </div>
  );
}