import httpClient from '@/shared/api/httpClient';
import type { SiteSettings, TeamMember } from '../types/about';
import type { PaginatedResponse } from '@/shared/types/api';
import type { AdminUser } from '@/features/users/types/user';

export const aboutApi = {
  // Site Settings
  getSettings: async (): Promise<{ data: SiteSettings }> => {
    const response = await httpClient.get('/admin/about/settings');
    return response.data;
  },
  updateSettings: async (settings: Partial<SiteSettings>): Promise<{ message: string; data: SiteSettings }> => {
    const response = await httpClient.put('/admin/about/settings', settings);
    return response.data;
  },
  uploadStoryImage: async (file: File): Promise<string> => {
    const formData = new FormData();
    formData.append('story_image', file);
    const response = await httpClient.post<{ url: string }>('/admin/about/upload-story-image', formData);
    return response.data.url;
  },
  deleteStoryImage: async (url: string): Promise<void> => {
    await httpClient.delete('/admin/about/delete-story-image', {
      data: { url },
    });
  },

  // Team Members
  getTeamMembers: async (page = 1, perPage = 10): Promise<PaginatedResponse<TeamMember>> => {
    const response = await httpClient.get('/admin/about/team-members', { params: { page, per_page: perPage } });
    return response.data;
  },
  getEligibleUsers: async (search = '', page = 1, perPage = 500): Promise<PaginatedResponse<AdminUser>> => {
    const response = await httpClient.get('/admin/about/eligible-users', { params: { search, page, per_page: perPage } });
    return response.data;
  },
  createTeamMember: async (payload: { user_id: string; sort_order: number; is_active: boolean }): Promise<{ message: string; data: TeamMember }> => {
    const response = await httpClient.post('/admin/about/team-members', payload);
    return response.data;
  },
  updateTeamMember: async (id: string, payload: { user_id?: string; sort_order?: number; is_active?: boolean }): Promise<{ message: string; data: TeamMember }> => {
    const response = await httpClient.put(`/admin/about/team-members/${id}`, payload);
    return response.data;
  },
  deleteTeamMember: async (id: string): Promise<{ message: string }> => {
    const response = await httpClient.delete(`/admin/about/team-members/${id}`);
    return response.data;
  },
};