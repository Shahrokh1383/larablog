import { useEffect } from 'react';
import { useMutation } from '@tanstack/react-query';
import { readerApi } from '../api/readerApi';

export function useTrackPostRead(postId: string | undefined) {
  const mutation = useMutation({
    mutationFn: (id: string) => readerApi.trackRead(id),
  });

  useEffect(() => {
    // Fire and forget when postId is available
    if (postId) {
      mutation.mutate(postId);
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [postId]);
}