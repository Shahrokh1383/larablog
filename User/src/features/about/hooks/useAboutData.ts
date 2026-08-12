import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { aboutApi } from '../api/aboutApi';

export const aboutKeys = {
  all: ['about'] as const,
  team: (page: number) => [...aboutKeys.all, 'team', page] as const,
};

export function useAboutData() {
  const [teamPage, setTeamPage] = useState(1);

  const query = useQuery({
    queryKey: aboutKeys.team(teamPage),
    queryFn: () => aboutApi.getAboutData(teamPage),
  });

  return { ...query, teamPage, setTeamPage };
}