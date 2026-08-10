import httpClient from '@/shared/api/httpClient';
import type { Comment } from '../types/comment';
import type { PaginatedResponse, ApiResponse } from '@/shared/types/api';

export const commentsApi = {
  getByPost: async (postId: string, page = 1): Promise<PaginatedResponse<Comment>> => {
    const response = await httpClient.get<PaginatedResponse<Comment>>(
      `/admin/posts/${postId}/comments`,
      { params: { page } }
    );
    return response.data;
  },

  reply: async (postId: string, body: string, parentId?: string): Promise<Comment> => {
    const response = await httpClient.post<ApiResponse<Comment>>(
      `/admin/posts/${postId}/comments`,
      { body, parent_id: parentId ?? null }
    );
    return response.data.data;
  },

  approve: async (commentId: string): Promise<void> => {
    await httpClient.patch(`/admin/comments/${commentId}/approve`);
  },

  delete: async (commentId: string): Promise<void> => {
    await httpClient.delete(`/admin/comments/${commentId}`);
  },
};