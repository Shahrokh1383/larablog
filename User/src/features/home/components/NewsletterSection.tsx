interface NewsletterSectionProps {
  email: string;
  onEmailChange: (value: string) => void;
  onSubmit: (e: React.FormEvent) => void;
  isPending: boolean;
  isSuccess: boolean;
  isError: boolean;
  message?: string;
}

export default function NewsletterSection({
  email,
  onEmailChange,
  onSubmit,
  isPending,
  isSuccess,
  isError,
  message,
}: NewsletterSectionProps) {
  return (
    <section className="newsletter-section section-padding">
      <div className="container">
        <div className="newsletter-wrapper">
          <div className="row align-items-center">
            <div className="col-lg-6">
              <div className="newsletter-content">
                <span className="section-badge">Newsletter</span>
                <h2 className="newsletter-title">Stay <span className="text-gradient">in the loop</span></h2>
                <p className="newsletter-desc">
                  Get the best articles delivered straight to your inbox every week.
                  No spam, just great content.
                </p>
              </div>
            </div>
            <div className="col-lg-6">
              <form className="newsletter-form" onSubmit={onSubmit}>
                <div className="newsletter-input-group">
                  <input 
                    type="email" 
                    className="newsletter-input" 
                    placeholder="Enter your email address..." 
                    required 
                    value={email}
                    onChange={(e) => onEmailChange(e.target.value)}
                    disabled={isPending || isSuccess}
                  />
                  <button 
                    type="submit" 
                    className="newsletter-btn" 
                    disabled={isPending || isSuccess}
                    style={isSuccess ? { background: 'linear-gradient(135deg, #10B981, #059669)' } : {}}
                  >
                    {isPending ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Subscribing...</span>
                      </>
                    ) : isSuccess ? (
                      <>
                        <i className="fa-sharp fa-solid fa-check me-2"></i>
                        <span>Subscribed!</span>
                      </>
                    ) : (
                      <>
                        <span>Subscribe</span>
                        <i className="fa-sharp fa-solid fa-paper-plane"></i>
                      </>
                    )}
                  </button>
                </div>
                {isError && (
                  <div className="mt-2 text-danger text-center" style={{ fontSize: '0.875rem' }}>
                    {message}
                  </div>
                )}
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}