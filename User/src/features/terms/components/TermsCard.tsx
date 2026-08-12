"use client";

import { useEffect, useRef } from "react";

interface TermsCardProps {
  title: string;
  children: React.ReactNode;
}

export function TermsCard({ title, children }: TermsCardProps) {
  const cardRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const card = cardRef.current;
    if (!card) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("revealed");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: "0px 0px -30px 0px" }
    );

    observer.observe(card);

    return () => {
      observer.disconnect();
    };
  }, []);

  return (
    <div ref={cardRef} className="terms-card">
      <h2>{title}</h2>
      {children}
    </div>
  );
}