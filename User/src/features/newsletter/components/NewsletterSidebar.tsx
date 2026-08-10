interface NewsletterSidebarProps {
  email: string;
  onEmailChange: (value: string) => void;
  onSubmit: (e: React.FormEvent) => void;
  isPending: boolean;
  isSuccess: boolean;
  isError: boolean;
  message?: string;
}

export default function NewsletterSidebar({
  email,
  onEmailChange,
  onSubmit,
  isPending,
  isSuccess,
  isError,
  message,
}: NewsletterSidebarProps) {
  return (
    <div className="sidebar-card newsletter-sidebar">
      <h4 className="sidebar-title">Newsletter</h4>
      <p>Get the best articles delivered to your inbox.</p>
      
      {isSuccess ? (
        <div className="alert alert-success mt-2 mb-0" role="alert">
          <i className="fa-sharp fa-solid fa-circle-check me-2"></i>
          {message || 'Successfully subscribed!'}
        </div>
      ) : (
        <form className="newsletter-sidebar-form" onSubmit={onSubmit}>
          <input 
            type="email" 
            className={`form-control ${isError ? 'is-invalid' : ''}`} 
            placeholder="your@email.com" 
            required 
            value={email}
            onChange={(e) => onEmailChange(e.target.value)}
            disabled={isPending}
          />
          {isError && <div className="invalid-feedback">{message}</div>}
          <button type="submit" className="btn btn-primary-custom w-100 mt-2" disabled={isPending}>
            {isPending ? (
              <>
                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Subscribing...
              </>
            ) : (
              'Subscribe'
            )}
          </button>
        </form>
      )}
    </div>
  );
}