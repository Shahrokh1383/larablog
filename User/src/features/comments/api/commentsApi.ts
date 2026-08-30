import httpClient from '@/shared/api/httpClient';
import type { ApiResponse } from '@/shared/types/api';
import type { Comment, CreateCommentPayload } from '../types/comment';

export interface CommentsResponse {
  data: Comment[];
  meta: {
    total: number;
    next_cursor: string | null;
    has_more: boolean;
  };
}

export interface RepliesResponse {
  data: Comment[];
  meta: {
    has_more: boolean;
  };
}

export const commentsApi = {
  getByPostId: async (postId: string, cursor: string | null = null): Promise<CommentsResponse> => {
    const response = await httpClient.get<CommentsResponse>(`/posts/${postId}/comments`, {
      params: { cursor },
    });
    return response.data;
  },

  getReplies: async (commentId: string, skip: number): Promise<RepliesResponse> => {
    const response = await httpClient.get<RepliesResponse>(`/comments/${commentId}/replies`, {
      params: { skip, take: 10 },
    });
    return response.data;
  },

  create: async (data: CreateCommentPayload): Promise<Comment> => {
    const response = await httpClient.post<ApiResponse<Comment>>('/comments', data);
    return response.data.data;
  },

  remove: async (commentId: string): Promise<void> => {
    await httpClient.delete(`/comments/${commentId}`);
  }
};