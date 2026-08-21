import '@/styles/taxonomy.css';
import CategoriesClientView from '@/features/categories/components/CategoriesClientView';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { Suspense } from 'react';

export const metadata = {
  title: 'All Categories | Larablog',
  description: 'Browse all article categories and find exactly what you are looking for.',
};

const jsonLd = {
  '@context': 'https://schema.org',
  '@type': 'CollectionPage',
  name: 'All Categories',
  description: 'Browse all article categories and find exactly what you are looking for.',
  url: '/category',
};

export default function CategoriesPage() {
  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />
      <Suspense
        fallback={
          <div className="text-center py-5">
            <div className="spinner-border text-primary"></div>
          </div>
        }
      >
        <CategoriesClientView>
          <BlogSidebar />
        </CategoriesClientView>
      </Suspense>
    </>
  );
}