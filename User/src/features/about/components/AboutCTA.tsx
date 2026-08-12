export default function AboutCTA() {
  return (
    <section className="cta-section section-padding">
      <div className="container">
        <div className="cta-wrapper">
          <div className="row align-items-center">
            <div className="col-lg-8">
              <h2 className="cta-title">Ready to join our <span className="text-gradient">community</span>?</h2>
              <p className="cta-desc">Start reading, commenting, and sharing your favorite articles today.</p>
            </div>
            <div className="col-lg-4 text-lg-end">
              <a href="/category" className="btn btn-primary-custom btn-lg">
                <span>Get Started</span>
                <i className="fa-sharp fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}