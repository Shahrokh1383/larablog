import Link from 'next/link';
import { Tag } from '../types/post';

interface PostTagsProps {
  tags: Tag[];
}

export default function PostTags({ tags }: PostTagsProps) {
  if (!tags || tags.length === 0) return null;

  return (
    <div className="post-tags">
      <span><i className="fa-sharp fa-solid fa-tags"></i> Tags:</span>
      {tags.map((tag) => (
        <Link key={tag.id} href={`/tag/${tag.slug}`} className="tag-link">
          {tag.name}
        </Link>
      ))}
    </div>
  );
}