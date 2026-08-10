import { useState } from 'react';
import { useContactMessages, useDeleteContactMessage } from '@/features/marketing/hooks/useMarketing';
import ContactMessageTable from '@/features/marketing/components/ContactMessageTable';

export default function ContactMessagesPage() {
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useContactMessages(page);
  const deleteMutation = useDeleteContactMessage();

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this message?')) {
      deleteMutation.mutate(id);
    }
  };

  return (
    <div className="container py-4">
      <h1 className="mb-4">Contact Messages</h1>

      <div className="card shadow-sm">
        <div className="card-body">
          {isLoading && <div className="text-center py-5"><div className="spinner-border" /></div>}
          {isError && <div className="alert alert-danger">Failed to load messages.</div>}
          
          {!isLoading && !isError && data?.data && (
            <ContactMessageTable 
              messages={data.data}
              onDelete={handleDelete}
              isLoading={deleteMutation.isPending}
            />
          )}

          {!isLoading && !isError && data?.data && data.data.length > 0 && (
            <div className="d-flex justify-content-center mt-4">
              <nav>
                <ul className="pagination">
                  <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => setPage(p => Math.max(p - 1, 1))}>Previous</button>
                  </li>
                  <li className="page-item active">
                    <span className="page-link">Page {data?.meta.current_page} of {data?.meta.last_page}</span>
                  </li>
                  <li className={`page-item ${page >= (data?.meta.last_page ?? 1) ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => setPage(p => p + 1)}>Next</button>
                  </li>
                </ul>
              </nav>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}