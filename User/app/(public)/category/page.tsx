import CategoriesClientView from '@/features/categories/components/CategoriesClientView';
import { BlogSidebar } from '@/shared/components/BlogSidebar';
import { Suspense } from 'react';

export const metadata = {
  title: 'All Categories | Larablog',
  description: 'Browse all article categories and find exactly what you are looking for.',
};

export default function CategoriesPage() {
  return (
    <section className="taxonomy-section section-padding">
      <div className="container">
        <div className="row">
          <div className="col-lg-8">
            <Suspense fallback={<div className="text-center py-5"><div className="spinner-border text-primary"></div></div>}>
              <CategoriesClientView />
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