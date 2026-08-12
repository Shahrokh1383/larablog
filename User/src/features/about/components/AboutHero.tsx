export default function AboutHero() {
  return (
    <section className="about-hero section-padding">
      <div className="about-hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
      </div>
      <div className="container text-center">
        <span className="section-badge animate-fade-in-up">
          <i className="fa-sharp fa-solid fa-circle-info"></i>
          About Us
        </span>
        <h1 className="about-hero-title animate-fade-in-up delay-1">
          We&apos;re on a mission to <span className="text-gradient">share knowledge</span> with the world
        </h1>
        <p className="about-hero-subtitle animate-fade-in-up delay-2">
          LaraBlog was born from a simple idea: everyone has a story, and every story deserves to be heard. 
          We provide a platform where writers, thinkers, and creators can connect and inspire.
        </p>
        <div className="about-hero-actions animate-fade-in-up delay-3">
          <a href="/category" className="btn btn-primary-custom btn-lg">
            <span>Explore Articles</span>
            <i className="fa-sharp fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </section>
  );
}