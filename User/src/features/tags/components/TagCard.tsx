import Link from 'next/link';
import type { Tag } from '../types/tag';

const getTagIcon = (slug: string): string => {
  const s = slug.toLowerCase();
  if (s.includes('laravel') || s.includes('php')) return 'fa-brands fa-laravel';
  if (s.includes('vue')) return 'fa-brands fa-vuejs';
  if (s.includes('react')) return 'fa-brands fa-react';
  if (s.includes('node')) return 'fa-brands fa-node-js';
  if (s.includes('docker')) return 'fa-brands fa-docker';
  if (s.includes('typescript') || s.includes('javascript') || s.includes('js')) return 'fa-brands fa-js';
  if (s.includes('python')) return 'fa-brands fa-python';
  if (s.includes('github')) return 'fa-brands fa-github';
  if (s.includes('aws')) return 'fa-brands fa-aws';
  if (s.includes('database') || s.includes('sql')) return 'fa-solid fa-database';
  if (s.includes('security')) return 'fa-solid fa-shield-halved';
  if (s.includes('testing')) return 'fa-solid fa-flask';
  if (s.includes('devops')) return 'fa-solid fa-infinity';
  if (s.includes('ai') || s.includes('ml')) return 'fa-solid fa-robot';
  if (s.includes('clean-code')) return 'fa-solid fa-feather';
  return 'fa-solid fa-hashtag';
};

interface TagCardProps {
  tag: Tag;
}

export default function TagCard({ tag }: TagCardProps) {
  return (
    <div className="col-md-4 col-6 tag-card-col" data-tag={tag.slug}>
      <Link href={`/tags/${tag.slug}`} className="taxonomy-card">
        <div className="taxonomy-card-icon">
          <i className={getTagIcon(tag.slug)}></i>
        </div>
        <h4 className="taxonomy-card-name">{tag.name}</h4>
        <span className="taxonomy-card-count">{tag.posts_count} articles</span>
      </Link>
    </div>
  );
}