import type { FaqItem } from '../types/contact';

interface FaqAccordionProps {
  items: FaqItem[];
  activeIndex: number | null;
  onToggle: (index: number) => void;
}

export default function FaqAccordion({ items, activeIndex, onToggle }: FaqAccordionProps) {
  return (
    <div className="faq-accordion" id="faqAccordion">
      {items.map((item, index) => (
        <div className="faq-item" key={index}>
          <button 
            className="faq-question" 
            aria-expanded={activeIndex === index}
            onClick={() => onToggle(index)}
          >
            <span>{item.question}</span>
            <i className="fa-sharp fa-solid fa-plus"></i>
          </button>
          <div className="faq-answer">
            <p>{item.answer}</p>
          </div>
        </div>
      ))}
    </div>
  );
}