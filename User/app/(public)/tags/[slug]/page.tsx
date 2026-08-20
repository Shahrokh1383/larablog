import TagPostsClientView from '@/features/tags/components/TagPostsClientView';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { Suspense } from 'react';
import { notFound } from 'next/navigation';

async function getTagMeta(slug: string) {
  try {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
    const res = await fetch(`${apiUrl}/tags/${slug}/posts?per_page=1`, { cache: 'no-store' });
    if (!res.ok) return null;
    const data = await res.json();
    return data.tag;
  } catch { return null; }
}

export async function generateMetadata({ params }: { params: { slug: string } }) {
  const tag = await getTagMeta(params.slug);
  return {
    title: tag ? `${tag.name} Articles | Larablog` : 'Tag | Larablog',
    description: tag ? `Explore articles and tutorials tagged with ${tag.name}.` : '',
  };
}

export default async function TagSlugPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const tag = await getTagMeta(slug);
  
  if (!tag) notFound();

  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    'name': tag.name,
    'description': `Explore articles and tutorials tagged with ${tag.name}.`,
    'url': `/tags/${slug}`,
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      <section className="taxonomy-section section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <Suspense fallback={<div className="text-center py-5"><div className="spinner-border text-primary"></div></div>}>
                <TagPostsClientView slug={slug} tagData={tag} />
              </Suspense>
            </div>
            <aside className="col-lg-4">
              <BlogSidebar />
            </aside>
          </div>
        </div>
      </section>
    </>
  );
}