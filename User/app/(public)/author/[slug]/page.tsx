'use client';

import { use } from 'react';
import AuthorHero from '@/features/authors/components/AuthorHero';
import AuthorPostCard from '@/features/authors/components/AuthorPostCard';
import AuthorSidebar from '@/features/authors/components/AuthorSidebar';
import Pagination from '@/shared/components/Pagination';
import { useAuthor } from '@/features/authors/hooks/useAuthor';
import { useAuthorPosts } from '@/features/authors/hooks/useAuthorPosts';
import { usePopularTags } from '@/features/tags/hooks/usePopularTags';
import '@/styles/author.css';

export default function AuthorProfilePage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  
  const { data: author, isLoading: authorLoading, isError: authorError } = useAuthor(slug);
  const { page, setPage, posts, totalPages, isLoading: postsLoading } = useAuthorPosts(slug);
  const { data: popularTags } = usePopularTags();

  if (authorLoading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary"></div>
      </div>
    );
  }

  if (authorError || !author) {
    return (
      <div className="container text-center py-5">
        <h1 className="text-danger">Author not found</h1>
        <p>The author you are looking for does not exist.</p>
      </div>
    );
  }

  return (
    <>
      <AuthorHero author={author} />

      <section className="author-articles section-padding">
        <div className="container">
          <div className="row">
            <div className="col-lg-8">
              <div className="section-header">
                <h2 className="section-title">
                  Articles by <span className="text-gradient">{author.name.split(' ')[0]}</span>
                </h2>
                <p className="section-desc">Browse all articles written by this author</p>
              </div>

              {postsLoading ? (
                <div className="text-center py-5">
                  <div className="spinner-border text-primary"></div>
                </div>
              ) : posts.length === 0 ? (
                <div className="text-center mt-4">
                  <i className="fa-sharp fa-solid fa-circle-exclamation fa-2x text-muted mb-3"></i>
                  <h5>No articles found</h5>
                  <p className="text-muted">This author hasn't published any articles yet.</p>
                </div>
              ) : (
                <>
                  <div className="row g-4" id="authorPostsGrid">
                    {posts.map((post) => (
                      <AuthorPostCard key={post.id} post={post} />
                    ))}
                  </div>
                  <Pagination currentPage={page} lastPage={totalPages} onPageChange={setPage} />
                </>
              )}
            </div>

            <AuthorSidebar author={author} popularTags={popularTags} />
          </div>
        </div>
      </section>
    </>
  );
}