import type { ContactFormData } from '../types/contact';

interface ContactFormProps {
  formData: ContactFormData;
  onChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => void;
  onSubmit: (e: React.FormEvent) => void;
  isAuthenticated: boolean;
  isPending: boolean;
  isSuccess: boolean;
  isError: boolean;
  errorMessage?: string;
  successMessage?: string;
}

export default function ContactForm({
  formData,
  onChange,
  onSubmit,
  isAuthenticated,
  isPending,
  isSuccess,
  isError,
  errorMessage,
  successMessage,
}: ContactFormProps) {
  return (
    <div className="contact-form-wrapper">
      <h2 className="form-title">Send a Message</h2>
      <p className="form-desc">We usually reply within 24 hours.</p>
      
      {isSuccess ? (
        <div className="form-success-message" id="formSuccess">
          <i className="fa-sharp fa-solid fa-circle-check"></i>
          <h3>Message Sent Successfully!</h3>
          <p>{successMessage || 'Thank you for contacting us. We will get back to you soon.'}</p>
        </div>
      ) : (
        <form id="contactForm" className="contact-form" onSubmit={onSubmit}>
          <div className="row g-4">
            {!isAuthenticated && (
              <>
                <div className="col-md-6">
                  <label className="form-label">Your Name *</label>
                  <input 
                    type="text" 
                    name="name"
                    className="form-control" 
                    required 
                    placeholder="John Doe"
                    value={formData.name}
                    onChange={onChange}
                  />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Email Address *</label>
                  <input 
                    type="email" 
                    name="email"
                    className="form-control" 
                    required 
                    placeholder="john@example.com"
                    value={formData.email}
                    onChange={onChange}
                  />
                </div>
              </>
            )}
            <div className="col-12">
              <label className="form-label">Subject</label>
              <input 
                type="text" 
                name="subject"
                className="form-control" 
                placeholder="How can we help?"
                value={formData.subject}
                onChange={onChange}
                required
              />
            </div>
            <div className="col-12">
              <label className="form-label">Message *</label>
              <textarea 
                name="message"
                className="form-control" 
                rows={6} 
                required 
                placeholder="Write your message here..."
                value={formData.message}
                onChange={onChange}
              ></textarea>
            </div>
            <div className="col-12">
              <div className="form-actions">
                <button type="submit" className="btn btn-primary-custom btn-lg" disabled={isPending}>
                  <span>{isPending ? 'Sending...' : 'Send Message'}</span>
                  {!isPending && <i className="fa-sharp fa-solid fa-paper-plane"></i>}
                  {isPending && <span className="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>}
                </button>
                <span className="form-note">We respect your privacy.</span>
              </div>
              {isError && <div className="alert alert-danger mt-3 mb-0">{errorMessage}</div>}
            </div>
          </div>
        </form>
      )}
    </div>
  );
}