'use client';

import ContactHero from '@/features/contact/components/ContactHero';
import ContactInfoCards from '@/features/contact/components/ContactInfoCards';
import ContactForm from '@/features/contact/components/ContactForm';
import ContactMap from '@/features/contact/components/ContactMap';
import FaqAccordion from '@/features/contact/components/FaqAccordion';
import { useSubmitContact } from '@/features/contact/hooks/useSubmitContact';
import { useFaqAccordion } from '@/features/contact/hooks/useFaqAccordion';
import { useScrollReveal } from '@/features/contact/hooks/useScrollReveal';
import type { FaqItem } from '@/features/contact/types/contact';
import '@/styles/contact.css';

const faqItems: FaqItem[] = [
  {
    question: "How can I become a contributor?",
    answer: "You can apply by sending us a sample of your writing through the contact form. Our editorial team reviews applications weekly."
  },
  {
    question: "Do you accept guest posts?",
    answer: "Absolutely! We welcome guest posts that align with our categories. Please review our submission guidelines and use the form above to pitch your idea."
  },
  {
    question: "Can I advertise on LaraBlog?",
    answer: "Yes, we offer various advertising options. Email us at ads@larablog.com for our media kit."
  },
  {
    question: "How do I report a problem?",
    answer: "Please use the contact form with subject \"Report a Problem\" or email support@larablog.com directly. We'll address it promptly."
  }
];

export default function ContactPage() {
  const contactFormState = useSubmitContact();
  const { activeIndex, toggle } = useFaqAccordion();
  
  // Triggers the IntersectionObserver for scroll animations
  useScrollReveal();

  return (
    <main>
      <ContactHero />
      <ContactInfoCards />
      
      <section className="contact-form-section section-padding bg-light-alt">
        <div className="container">
          <div className="row g-5">
            <div className="col-lg-7">
              <ContactForm {...contactFormState} />
            </div>
            <div className="col-lg-5">
              <ContactMap />
            </div>
          </div>
        </div>
      </section>

      <section className="faq-section section-padding">
        <div className="container">
          <div className="section-header text-center">
            <span className="section-badge">FAQ</span>
            <h2 className="section-title">Frequently Asked <span className="text-gradient">Questions</span></h2>
            <p className="section-desc">Quick answers to common inquiries</p>
          </div>
          <FaqAccordion items={faqItems} activeIndex={activeIndex} onToggle={toggle} />
        </div>
      </section>
    </main>
  );
}