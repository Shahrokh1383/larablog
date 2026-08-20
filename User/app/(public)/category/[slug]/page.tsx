import CategoryPostsClientView from '@/features/categories/components/CategoryPostsClientView';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { Suspense } from 'react';
import { notFound } from 'next/navigation';

// Lightweight server-side fetch for SEO metadata
async function getCategoryMeta(slug: string) {
  try {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/categories/${slug}/posts?per_page=1`, { cache: 'no-store' });
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

  return (
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
  );
}