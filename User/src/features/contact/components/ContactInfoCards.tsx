export default function ContactInfoCards() {
  return (
    <section className="contact-info-section">
      <div className="container">
        <div className="row g-4">
          <div className="col-md-4">
            <div className="contact-info-card">
              <div className="contact-info-icon">
                <i className="fa-sharp fa-solid fa-location-dot"></i>
              </div>
              <h3>Visit Us</h3>
              <p>123 Innovation Drive<br />Tech City, CA 94043</p>
            </div>
          </div>
          <div className="col-md-4">
            <div className="contact-info-card">
              <div className="contact-info-icon">
                <i className="fa-sharp fa-solid fa-envelope"></i>
              </div>
              <h3>Email Us</h3>
              <p>
                <a href="mailto:LaraBlog@gmail.com">LaraBlog@gmail.com</a><br />
                <a href="mailto:LaraBlogSuport@gmail.com">LaraBlogSupport@gmail.com</a>
              </p>
            </div>
          </div>
          <div className="col-md-4">
            <div className="contact-info-card">
              <div className="contact-info-icon">
                <i className="fa-sharp fa-solid fa-phone"></i>
              </div>
              <h3>Call Us</h3>
              <p>
                <a href="tel:+1234567890">+1 (234) 567-890</a><br />
                Mon-Fri, 9AM-5PM PST
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}