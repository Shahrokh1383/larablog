'use client';

import '@/styles/post.css';
import { useParams } from 'next/navigation';
import { usePost, useRelatedPosts } from '@/features/posts';
import { useCategories } from '@/features/categories';
import { 
  useComments, 
  useLoadMoreReplies,
  useDeleteComment,
  CommentsSection 
} from '@/features/comments';
import { 
  useTrackPostRead, 
  useToggleSavedPost, 
  SavePostButton 
} from '@/features/reader';
import { useSubscribeNewsletter } from '@/features/newsletter';

// Internal Post components
import PostBreadcrumb from '@/features/posts/components/PostBreadcrumb';
import PostHeader from '@/features/posts/components/PostHeader';
import PostFeaturedImage from '@/features/posts/components/PostFeaturedImage';
import PostBody from '@/features/posts/components/PostBody';
import PostTags from '@/features/posts/components/PostTags';
import AuthorBioCard from '@/features/posts/components/AuthorBioCard';
import PostSidebar from '@/features/posts/components/PostSidebar';

export default function PostPage() {
  const params = useParams();
  const slug = params.slug as string;

  const { data: post, isLoading, isError } = usePost(slug);
  const { data: relatedPosts } = useRelatedPosts(slug);

  const categoriesQuery = useCategories();
  const categories = categoriesQuery.data?.data;

  const commentsQuery = useComments(post?.id || '');
  const loadMoreReplies = useLoadMoreReplies(post?.id || '');
  const deleteComment = useDeleteComment(post?.id || '');

  useTrackPostRead(post?.id);
  const toggleSaveMutation = useToggleSavedPost(post?.id || '', slug);

  const newsletterState = useSubscribeNewsletter();

  if (isLoading) {
    return (
      <div className="container py-5 text-center">
        <div className="spinner-border text-primary"></div>
      </div>
    );
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
              <PostBreadcrumb category={post.category || null} title={post.title} />

              <PostHeader
                post={post}
                action={
                  <SavePostButton
                    isSaved={post.is_saved || false}
                    isLoading={toggleSaveMutation.isPending}
                    onToggle={() => toggleSaveMutation.mutate()}
                  />
                }
              />

              <PostFeaturedImage src={post.featured_image || ''} alt={post.title} />
              <PostBody post={post} />
              <PostTags tags={post.tags || []} />
              <AuthorBioCard author={post.author!} />
            </article>

            <CommentsSection
              postId={post.id}
              commentsQuery={commentsQuery}
              onLoadMoreReplies={loadMoreReplies.mutate}
              fetchingReplyId={loadMoreReplies.isPending ? loadMoreReplies.variables : null}
              onDeleteComment={deleteComment.mutate}
              deletingCommentId={deleteComment.isPending ? deleteComment.variables : null}
            />
          </div>

          <PostSidebar
            author={post.author!}
            relatedPosts={relatedPosts}
            categories={categories}
            newsletterState={newsletterState}
          />
        </div>
      </div>
    </main>
  );
}