import type { AboutData } from '@/features/about';

interface ContactInfoCardsProps {
  settings?: AboutData['settings'];
}

export default function ContactInfoCards({ settings }: ContactInfoCardsProps) {
  const phone = settings?.call_us_phone || 'N/A';
  const emails = settings?.call_us_emails || [];
  const address = settings?.visit_address || 'N/A';

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
              <p>{address}</p>
            </div>
          </div>
          <div className="col-md-4">
            <div className="contact-info-card">
              <div className="contact-info-icon">
                <i className="fa-sharp fa-solid fa-envelope"></i>
              </div>
              <h3>Email Us</h3>
              <p>
                {emails.length > 0 ? (
                  emails.map((email, index) => (
                    <span key={index} style={{ display: 'block' }}>
                      <a href={`mailto:${email}`}>{email}</a>
                    </span>
                  ))
                ) : (
                  'N/A'
                )}
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
                <a href={`tel:${phone}`}>{phone}</a><br />
                Mon-Fri, 9AM-5PM PST
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}