import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { authorsApi } from '../api/authorsApi';

export function useAuthorPosts(username: string) {
  const [page, setPage] = useState(1);

  const query = useQuery({
    queryKey: ['authors', 'posts', username, page],
    queryFn: () => authorsApi.getPosts(username, { page, per_page: 6 }),
    enabled: !!username,
    placeholderData: (previousData) => previousData,
  });

  return {
    page,
    setPage,
    posts: query.data?.data || [],
    totalPages: query.data?.meta?.last_page || 1,
    isLoading: query.isLoading,
    isError: query.isError,
  };
}