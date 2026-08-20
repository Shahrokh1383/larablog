import '@/styles/taxonomy.css';
import TagsClientView from '@/features/tags/components/TagsClientView';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { Suspense } from 'react';

export const metadata = {
  title: 'All Tags | Larablog',
  description: 'Browse all topics and tags to find exactly what you are looking for.',
};

const jsonLd = {
  '@context': 'https://schema.org',
  '@type': 'CollectionPage',
  'name': 'All Tags',
  'description': 'Browse all topics and tags to find exactly what you are looking for.',
  'url': '/tags',
};

export default function TagsPage() {
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
                <TagsClientView />
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