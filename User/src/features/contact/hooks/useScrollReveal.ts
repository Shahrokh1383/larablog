import { useEffect } from 'react';

export function useScrollReveal() {
  useEffect(() => {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target as HTMLElement;
          el.style.opacity = '0';
          el.style.transform = 'translateY(30px)';
          el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
          requestAnimationFrame(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
          });
          observer.unobserve(el);
        }
      });
    }, observerOptions);

    // Delay slightly to ensure DOM is fully painted by React before observing
    const timeoutId = setTimeout(() => {
      const elementsToAnimate = document.querySelectorAll('.contact-info-card, .contact-form-wrapper, .contact-map-wrapper, .faq-item');
      elementsToAnimate.forEach(el => observer.observe(el));
    }, 100);

    return () => {
      clearTimeout(timeoutId);
      observer.disconnect();
    };
  }, []);
}