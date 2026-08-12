'use client';

import { useCountUp } from '../hooks/useCountUp';

interface CountUpProps {
  target: number;
  duration?: number;
  suffix?: string;
  prefix?: string;
}

export default function CountUp({ target, duration = 2000, suffix = '', prefix = '' }: CountUpProps) {
  const { count, ref } = useCountUp(target, duration);

  return (
    <span className="stat-number" ref={ref}>
      {prefix}{count}{suffix}
    </span>
  );
}