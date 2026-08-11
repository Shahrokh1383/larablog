'use client';

import '@/styles/home.css';
import { useHomeData } from '@/features/home/hooks/useHomeData';
import { useSubscribeNewsletter } from '@/features/newsletter/hooks/useSubscribeNewsletter';

import HeroSection from '@/features/home/components/HeroSection';
import { FeaturedPosts } from '@/features/home/components/FeaturedPosts';
import { StandardPostCard } from '@/features/home/components/StandardPostCard';
import { CategoryCard } from '@/features/home/components/CategoryCard';
import NewsletterSection from '@/features/home/components/NewsletterSection';

export default function HomePage() {
  const { data, isLoading, isError } = useHomeData();
  const newsletter = useSubscribeNewsletter();

  if (isLoading) return <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>Loading...</div>;
  if (isError || !data) return <div>Error loading page data.</div>;

  return (
    <main>
      {/* Hero Section Component */}
      <HeroSection />

      {/* Featured Posts Section */}
      <section className="featured-section section-padding">
        <div className="container">
          <div className="section-header text-center">
            <span className="section-badge">Editor's Pick</span>
            <h2 className="section-title"><span className="text-gradient">Featured</span> Articles</h2>
            <p className="section-desc">Hand‑picked articles you can't afford to miss this week</p>
          </div>
          <FeaturedPosts posts={data.featured_posts} />
        </div>
      </section>

      {/* Recent Posts Section */}
      <section className="recent-section section-padding bg-light-alt">
        <div className="container">
          <div className="section-header text-center">
            <span className="section-badge">Latest</span>
            <h2 className="section-title"><span className="text-gradient">Recent</span> Posts</h2>
            <p className="section-desc">Fresh content from our talented writers</p>
          </div>
          <div className="row g-4">
            {data.recent_posts.map(post => (
              <div key={post.id} className="col-lg-4 col-md-6">
                <StandardPostCard post={post} />
              </div>
            ))}
          </div>
          <div className="text-center mt-5">
            <a href="/category" className="btn btn-primary-custom btn-lg">
              <span>View All Articles</span>
              <i className="fa-sharp fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="categories-section section-padding">
        <div className="container">
          <div className="section-header text-center">
            <span className="section-badge">Topics</span>
            <h2 className="section-title">Explore <span className="text-gradient">Categories</span></h2>
            <p className="section-desc">Browse articles by your favorite topics</p>
          </div>
          <div className="row g-4">
            {data.categories.map(cat => (
              <div key={cat.id} className="col-lg-3 col-md-4 col-6">
                <CategoryCard category={cat} />
              </div>
            ))}
            <div className="col-lg-3 col-md-4 col-6">
              <CategoryCard isMoreCard totalPostsCount={data.total_posts_count} />
            </div>
          </div>
        </div>
      </section>

      {/* Newsletter Section */}
      <NewsletterSection 
        email={newsletter.email}
        onEmailChange={newsletter.onEmailChange}
        onSubmit={newsletter.onSubmit}
        isPending={newsletter.isPending}
        isSuccess={newsletter.isSuccess}
        isError={newsletter.isError}
        message={newsletter.message}
      />
    </main>
  );
}