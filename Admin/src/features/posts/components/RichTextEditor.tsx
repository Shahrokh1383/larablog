import { useRef, useEffect } from 'react';
import { postsApi } from '../api/postsApi';

interface RichTextEditorProps {
  value: string;
  onChange: (html: string) => void;
}

export default function RichTextEditor({ value, onChange }: RichTextEditorProps) {
  const editorRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Set initial content only once
  useEffect(() => {
    if (editorRef.current && value && editorRef.current.innerHTML !== value) {
      editorRef.current.innerHTML = value;
    }
  }, [value]);

  const exec = (command: string, val?: string) => {
    document.execCommand(command, false, val);
    if (editorRef.current) onChange(editorRef.current.innerHTML);
  };

  const handleImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      const url = await postsApi.uploadImage(file);
      exec('insertImage', url);
    } catch (error) {
      alert('Image upload failed.');
    }
  };

  const buttonClass = "btn btn-sm btn-outline-secondary";

  return (
    <div className="border rounded">
      <div className="d-flex flex-wrap gap-1 p-2 border-bottom bg-light">
        <button type="button" className={buttonClass} onClick={() => exec('bold')}><i className="fas fa-bold"></i></button>
        <button type="button" className={buttonClass} onClick={() => exec('italic')}><i className="fas fa-italic"></i></button>
        <button type="button" className={buttonClass} onClick={() => exec('insertUnorderedList')}><i className="fas fa-list-ul"></i></button>
        <button type="button" className={buttonClass} onClick={() => exec('insertOrderedList')}><i className="fas fa-list-ol"></i></button>
        <button type="button" className={buttonClass} onClick={() => exec('formatBlock', 'h2')}><i className="fas fa-heading"></i></button>
        <button type="button" className={buttonClass} onClick={() => exec('formatBlock', 'blockquote')}><i className="fas fa-quote-right"></i></button>
        <button type="button" className={buttonClass} onClick={() => exec('formatBlock', 'pre')}><i className="fas fa-code"></i></button>
        <button type="button" className={buttonClass} onClick={() => fileInputRef.current?.click()}>
          <i className="fas fa-image"></i>
        </button>
        <input type="file" ref={fileInputRef} className="d-none" accept="image/*" onChange={handleImageUpload} />
      </div>
      <div
        ref={editorRef}
        contentEditable
        className="p-3"
        style={{ minHeight: '300px', outline: 'none' }}
        onInput={(e) => onChange(e.currentTarget.innerHTML)}
      />
    </div>
  );
}