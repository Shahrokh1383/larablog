import httpClient from '@/shared/api/httpClient';
import type { Post, PostFormData } from '../types/post';
import type { PaginatedResponse, ApiResponse } from '@/shared/types/api';

export const postsApi = {
  getAll: async (page = 1, search = '', isEditorsPick?: boolean): Promise<PaginatedResponse<Post>> => {
    const params: Record<string, string | number | boolean> = { page, search };
    if (isEditorsPick !== undefined) {
      params.is_editors_pick = isEditorsPick;
    }
    const response = await httpClient.get<PaginatedResponse<Post>>('/admin/posts', { params });
    return response.data;
  },

  getById: async (id: string): Promise<Post> => {
    const response = await httpClient.get<ApiResponse<Post>>(`/admin/posts/${id}`);
    return response.data.data;
  },

  create: async (data: PostFormData): Promise<Post> => {
    const response = await httpClient.post<ApiResponse<Post>>('/admin/posts', data);
    return response.data.data;
  },

  update: async (id: string, data: Partial<PostFormData>): Promise<Post> => {
    const response = await httpClient.put<ApiResponse<Post>>(`/admin/posts/${id}`, data);
    return response.data.data;
  },

  delete: async (id: string): Promise<void> => {
    await httpClient.delete(`/admin/posts/${id}`);
  },

  uploadImage: async (file: File): Promise<string> => {
    const formData = new FormData();
    formData.append('image', file);
    const response = await httpClient.post<{ url: string }>('/admin/posts/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data.url;
  },

  deleteImage: async (url: string): Promise<void> => {
    await httpClient.delete('/admin/posts/delete-image', {
      data: { url },
    });
  },
};