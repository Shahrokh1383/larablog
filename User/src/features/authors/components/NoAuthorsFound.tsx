export default function NoAuthorsFound() {
  return (
    <div className="text-center mt-5">
      <i className="fa-sharp fa-solid fa-user-slash fa-3x text-muted mb-3"></i>
      <h4>No authors found</h4>
      <p className="text-muted">Try a different name or role.</p>
    </div>
  );
}