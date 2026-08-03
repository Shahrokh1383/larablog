'use client';

import { useParams } from 'next/navigation';
import { usePost } from '@/features/posts/hooks/usePost';
import { useRelatedPosts } from '@/features/posts/hooks/useRelatedPosts';
import { useCategories } from '@/features/categories/hooks/useCategories';
import PostBreadcrumb from '@/features/posts/components/PostBreadcrumb';
import PostHeader from '@/features/posts/components/PostHeader';
import PostFeaturedImage from '@/features/posts/components/PostFeaturedImage';
import PostBody from '@/features/posts/components/PostBody';
import PostTags from '@/features/posts/components/PostTags';
import AuthorBioCard from '@/features/posts/components/AuthorBioCard';
import CommentsSection from '@/features/posts/components/CommentsSection';
import PostSidebar from '@/features/posts/components/PostSidebar';
import '@/styles/post.css';

export default function PostPage() {
  const params = useParams();
  const slug = params.slug as string;

  const { data: post, isLoading, isError } = usePost(slug);
  const { data: relatedPosts } = useRelatedPosts(slug);
  const { data: categories } = useCategories();

  if (isLoading) {
    return <div className="container py-5 text-center"><div className="spinner-border text-primary"></div></div>;
  }

  if (isError || !post) {
    return <div className="container py-5 text-center">Error loading post.</div>;
  }

  return (
    <main className="post-main">
      <div className="container">
        <div className="row g-5">
          <div className="col-lg-8">
            <article className="post-article">
              <PostBreadcrumb category={post.category} title={post.title} />
              <PostHeader post={post} />
              <PostFeaturedImage src={post.featured_image} alt={post.title} />
              <PostBody post={post} />
              <PostTags tags={post.tags} />
              <AuthorBioCard author={post.author} />
            </article>

            <CommentsSection />
          </div>

          <PostSidebar 
            author={post.author} 
            relatedPosts={relatedPosts} 
            categories={categories}
          />
        </div>
      </div>
    </main>
  );
}