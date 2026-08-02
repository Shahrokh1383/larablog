import { useState, useRef, useEffect } from 'react';
import type { Tag } from '@/features/tags/types/tag';

interface MultiSelectTagsProps {
  tags: Tag[];
  selectedIds: string[];
  onChange: (ids: string[]) => void;
}

export default function MultiSelectTags({ tags, selectedIds, onChange }: MultiSelectTagsProps) {
  const [search, setSearch] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const filteredTags = tags.filter(tag =>
    tag.name.toLowerCase().includes(search.toLowerCase())
  );

  const toggleTag = (id: string) => {
    if (selectedIds.includes(id)) {
      onChange(selectedIds.filter(t => t !== id));
    } else {
      onChange([...selectedIds, id]);
    }
  };

  return (
    <div ref={containerRef} style={{ position: 'relative' }}>
      <div className="form-control d-flex flex-wrap gap-1" style={{ minHeight: '38px' }} onClick={() => setIsOpen(true)}>
        {selectedIds.length === 0 && <span className="text-muted">Select tags...</span>}
        {selectedIds.map(id => {
          const tag = tags.find(t => t.id === id);
          return (
            <span key={id} className="badge bg-primary d-flex align-items-center">
              {tag?.name}
              <button type="button" className="btn-close btn-close-white btn-sm ms-1" onClick={(e) => { e.stopPropagation(); toggleTag(id); }}></button>
            </span>
          );
        })}
      </div>
      
      {isOpen && (
        <div className="border rounded shadow-sm bg-white" style={{ position: 'absolute', top: '100%', left: 0, right: 0, zIndex: 1000, marginTop: '2px' }}>
          <input
            type="text"
            className="form-control border-0"
            placeholder="Search tags..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            autoFocus
          />
          <div className="list-group list-group-flush" style={{ maxHeight: '200px', overflowY: 'auto' }}>
            {filteredTags.length === 0 && <div className="list-group-item text-muted">No tags found.</div>}
            {filteredTags.map(tag => (
              <button
                key={tag.id}
                type="button"
                className={`list-group-item list-group-item-action ${selectedIds.includes(tag.id) ? 'active' : ''}`}
                onClick={() => toggleTag(tag.id)}
              >
                {tag.name}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}