export default function ContactHero() {
  return (
    <section className="contact-hero section-padding">
      <div className="contact-hero-bg">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
      </div>
      <div className="container text-center">
        <span className="section-badge animate-fade-in-up">
          <i className="fa-sharp fa-solid fa-envelope"></i>
          Get in Touch
        </span>
        <h1 className="contact-hero-title animate-fade-in-up delay-1">
          We'd <span className="text-gradient">love to hear</span> from you
        </h1>
        <p className="contact-hero-subtitle animate-fade-in-up delay-2">
          Have a question, suggestion, or just want to say hello? Fill out the form below or reach us directly.
        </p>
      </div>
    </section>
  );
}