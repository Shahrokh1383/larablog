import CountUp from './CountUp';

export default function HeroSection() {
  return (
    <section className="hero-section" id="heroSection">
      {/* Animated Background Shapes */}
      <div className="hero-bg-shapes">
        <div className="shape shape-1"></div>
        <div className="shape shape-2"></div>
        <div className="shape shape-3"></div>
        <div className="shape shape-4"></div>
        <div className="shape shape-5"></div>
        <div className="shape shape-6"></div>
      </div>

      <div className="container hero-container">
        <div className="row align-items-center min-vh-85">
          <div className="col-lg-7 hero-content-col">
            <span className="hero-badge animate-fade-in-up">
              <i className="fa-sharp fa-solid fa-sparkles"></i>
              Welcome to LaraBlog
            </span>
            <h1 className="hero-title animate-fade-in-up delay-1">
              Explore a world of <span className="text-gradient">knowledge &amp; creativity</span>
            </h1>
            <p className="hero-subtitle animate-fade-in-up delay-2">
              Where ideas are born, stories are told, and knowledge is shared.
              Join our community of passionate writers and curious minds.
            </p>
            <div className="hero-actions animate-fade-in-up delay-3">
              <a href="/category" className="btn btn-primary-custom btn-lg">
                <span>Start Reading</span>
                <i className="fa-sharp fa-solid fa-arrow-right"></i>
              </a>
              <a href="/about" className="btn btn-outline-custom btn-lg">
                <span>Learn More</span>
                <i className="fa-sharp fa-solid fa-circle-info"></i>
              </a>
            </div>
            <div className="hero-stats animate-fade-in-up delay-4">
              <div className="stat-item">
                <CountUp target={250} suffix="+" />
                <span className="stat-label">Articles</span>
              </div>
              <div className="stat-divider"></div>
              <div className="stat-item">
                <CountUp target={20} suffix="+" />
                <span className="stat-label">Authors</span>
              </div>
              <div className="stat-divider"></div>
              <div className="stat-item">
                <CountUp target={50} suffix="K+" />
                <span className="stat-label">Monthly Readers</span>
              </div>
            </div>
          </div>
          <div className="col-lg-5 hero-visual-col d-none d-lg-block">
            <div className="hero-visual">
              <div className="hero-floating-card card-1">
                <i className="fa-sharp fa-solid fa-code"></i>
                <span>Development</span>
              </div>
              <div className="hero-floating-card card-2">
                <i className="fa-sharp fa-solid fa-palette"></i>
                <span>Design</span>
              </div>
              <div className="hero-floating-card card-3">
                <i className="fa-sharp fa-solid fa-chart-line"></i>
                <span>Business</span>
              </div>
              <div className="hero-main-icon">
                <i className="fa-sharp fa-solid fa-blog"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Scroll Indicator */}
      <div className="scroll-indicator">
        <span>Scroll down</span>
        <div className="scroll-mouse">
          <div className="scroll-wheel"></div>
        </div>
      </div>
    </section>
  );
}