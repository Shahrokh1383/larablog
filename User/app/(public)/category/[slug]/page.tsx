import '@/styles/taxonomy.css';
import CategoryPostsClientView from '@/features/categories/components/CategoryPostsClientView';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { Suspense } from 'react';
import { notFound } from 'next/navigation';

async function getCategoryMeta(slug: string) {
  try {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
    const res = await fetch(`${apiUrl}/categories/${slug}/posts?per_page=1`, { cache: 'no-store' });
    if (!res.ok) return null;
    const data = await res.json();
    return data.category;
  } catch { return null; }
}

export async function generateMetadata({ params }: { params: { slug: string } }) {
  const category = await getCategoryMeta(params.slug);
  return {
    title: category ? `${category.name} Articles | Larablog` : 'Category | Larablog',
    description: category ? `Explore the latest articles and insights on ${category.name}.` : '',
  };
}

export default async function CategorySlugPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const category = await getCategoryMeta(slug);
  
  if (!category) notFound();

  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    'name': category.name,
    'description': `Explore the latest articles and insights on ${category.name}.`,
    'url': `/category/${slug}`,
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
                <CategoryPostsClientView slug={slug} categoryData={category} />
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