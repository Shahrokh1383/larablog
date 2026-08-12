import { TeamMember } from '../types/about';
import TeamMemberCard from './TeamMemberCard';

interface AboutTeamProps {
  members: TeamMember[];
}

export default function AboutTeam({ members }: AboutTeamProps) {
  return (
    <section className="team-section section-padding bg-light-alt">
      <div className="container">
        <div className="section-header text-center">
          <span className="section-badge">Our Team</span>
          <h2 className="section-title">Meet the <span className="text-gradient">People</span> Behind LaraBlog</h2>
          <p className="section-desc">A passionate group of writers, developers, and designers</p>
        </div>
        <div className="row g-4 justify-content-center">
          {members.map((member) => (
            <TeamMemberCard key={member.id} member={member} />
          ))}
        </div>
      </div>
    </section>
  );
}