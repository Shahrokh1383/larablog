import { useState } from 'react';
import { useSubscribers, useDeleteSubscriber, useSendNewsletter, useToggleSubscriberStatus } from '@/features/marketing/hooks/useMarketing';
import SubscriberTable from '@/features/marketing/components/SubscriberTable';

export default function SubscribersPage() {
  const [page, setPage] = useState(1);
  const [selectedIds, setSelectedIds] = useState<string[]>([]);
  
  const { data, isLoading, isError } = useSubscribers(page);
  const deleteMutation = useDeleteSubscriber();
  const sendMutation = useSendNewsletter();
  const toggleStatusMutation = useToggleSubscriberStatus();

  const handleToggleSelect = (id: string) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
  };

  const handleSelectAll = () => {
    if (!data?.data) return;
    const allIds = data.data.map(s => s.id);
    const allSelected = allIds.every(id => selectedIds.includes(id));
    setSelectedIds(allSelected ? [] : allIds);
  };

  const handleDelete = (id: string) => {
    if (confirm('Are you sure you want to delete this subscriber?')) {
      deleteMutation.mutate(id);
    }
  };

  const handleToggleStatus = (id: string) => {
    toggleStatusMutation.mutate(id);
  };

  const handleSendNewsletter = (sendToAll: boolean) => {
    if (!sendToAll && selectedIds.length === 0) {
      alert('Please select at least one subscriber or choose "Send to All".');
      return;
    }
    
    if (confirm(`Send newsletter to ${sendToAll ? 'ALL active subscribers' : `${selectedIds.length} selected subscribers`}?`)) {
      sendMutation.mutate({ 
        send_to_all: sendToAll, 
        subscriber_ids: sendToAll ? undefined : selectedIds 
      }, {
        onSuccess: () => {
          alert('Newsletter job dispatched successfully!');
          setSelectedIds([]);
        }
      });
    }
  };

  return (
    <div className="container py-4">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Subscribers</h1>
        <div className="d-flex gap-2">
          <button 
            className="btn btn-outline-primary" 
            onClick={() => handleSendNewsletter(false)}
            disabled={sendMutation.isPending || selectedIds.length === 0}
          >
            Send to Selected ({selectedIds.length})
          </button>
          <button 
            className="btn btn-primary" 
            onClick={() => handleSendNewsletter(true)}
            disabled={sendMutation.isPending}
          >
            Send to All
          </button>
        </div>
      </div>

      <div className="card shadow-sm">
        <div className="card-body">
          {isLoading && <div className="text-center py-5"><div className="spinner-border" /></div>}
          {isError && <div className="alert alert-danger">Failed to load subscribers.</div>}
          
          {!isLoading && !isError && data?.data && (
            <SubscriberTable 
              subscribers={data.data}
              selectedIds={selectedIds}
              onToggleSelect={handleToggleSelect}
              onSelectAll={handleSelectAll}
              onDelete={handleDelete}
              onToggleStatus={handleToggleStatus}
              isLoading={deleteMutation.isPending}
              isToggling={toggleStatusMutation.isPending}
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