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

  // Team Members
  getTeamMembers: async (page = 1, perPage = 10): Promise<PaginatedResponse<TeamMember>> => {
    const response = await httpClient.get('/admin/about/team-members', { params: { page, per_page: perPage } });
    return response.data;
  },
  getEligibleUsers: async (): Promise<{ data: AdminUser[] }> => {
    const response = await httpClient.get('/admin/about/eligible-users');
    return response.data;
  },
  createTeamMember: async (formData: FormData): Promise<{ message: string; data: TeamMember }> => {
    const response = await httpClient.post('/admin/about/team-members', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },
  updateTeamMember: async (id: string, formData: FormData): Promise<{ message: string; data: TeamMember }> => {
    const response = await httpClient.put(`/admin/about/team-members/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },
  deleteTeamMember: async (id: string): Promise<{ message: string }> => {
    const response = await httpClient.delete(`/admin/about/team-members/${id}`);
    return response.data;
  },
};