import { useQuery } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export function useTeamMembers(page = 1, perPage = 10) {
  return useQuery({
    queryKey: ['about', 'team-members', page, perPage],
    queryFn: () => aboutApi.getTeamMembers(page, perPage),
  });
}