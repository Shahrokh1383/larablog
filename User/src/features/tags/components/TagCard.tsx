import Link from 'next/link';

interface TagCardProps {
  tag: any;
}

export default function TagCard({ tag }: TagCardProps) {
  return (
    <div className="col-md-4 col-6 tag-card-col" data-tag={tag.slug}>
      <Link href={`/tags?search=${tag.slug}`} className="tag-card">
        <div className="tag-card-icon"><i className="fa-sharp fa-solid fa-hashtag"></i></div>
        <h4 className="tag-card-name">{tag.name}</h4>
        <span className="tag-card-count">{tag.posts_count} articles</span>
      </Link>
    </div>
  );
}