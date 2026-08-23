import { useRef, useEffect, useState, useCallback } from 'react';

interface RichTextEditorProps {
  value: string;
  onChange: (html: string) => void;
  onImageUpload: (file: File) => Promise<string | undefined>;
  isUploadingImage: boolean;
}

export default function RichTextEditor({ value, onChange, onImageUpload, isUploadingImage }: RichTextEditorProps) {
  const editorRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [activeStates, setActiveStates] = useState<Record<string, boolean>>({});

  useEffect(() => {
    if (editorRef.current) {
      if (value && editorRef.current.innerHTML !== value) {
        editorRef.current.innerHTML = value;
      } else if (!value && editorRef.current.innerHTML === '') {
        editorRef.current.innerHTML = '<p><br></p>';
      }
    }
  }, [value]);

  const updateActiveStates = useCallback(() => {
    if (!editorRef.current) return;
    
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) {
      setActiveStates({});
      return;
    }

    let node = selection.anchorNode;
    let isInsideEditor = false;
    while (node) {
      if (node === editorRef.current) {
        isInsideEditor = true;
        break;
      }
      node = node.parentNode;
    }

    if (!isInsideEditor) {
      setActiveStates({});
      return;
    }

    const states: Record<string, boolean> = {
      bold: document.queryCommandState('bold'),
      italic: document.queryCommandState('italic'),
      insertUnorderedList: document.queryCommandState('insertUnorderedList'),
      insertOrderedList: document.queryCommandState('insertOrderedList'),
      h2: false,
      blockquote: false,
      pre: false,
    };

    let currentBlock = selection.anchorNode;
    if (currentBlock && currentBlock.nodeType === 3) {
      currentBlock = currentBlock.parentNode;
    }

    const blockTags = ['H2', 'BLOCKQUOTE', 'PRE'];
    while (currentBlock && currentBlock !== editorRef.current) {
      if (currentBlock.nodeName && blockTags.includes(currentBlock.nodeName)) {
        if (currentBlock.nodeName === 'H2') states.h2 = true;
        if (currentBlock.nodeName === 'BLOCKQUOTE') states.blockquote = true;
        if (currentBlock.nodeName === 'PRE') states.pre = true;
        break;
      }
      currentBlock = currentBlock.parentNode;
    }

    setActiveStates(states);
  }, []);

  const exec = (command: string, val?: string) => {
    document.execCommand(command, false, val);
    if (editorRef.current) {
      onChange(editorRef.current.innerHTML);
      updateActiveStates();
    }
  };

  const handleMouseDown = (e: React.MouseEvent, command: string, val?: string) => {
    e.preventDefault();
    exec(command, val);
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      const selection = window.getSelection();
      if (!selection || !selection.rangeCount || !editorRef.current) return;

      let node = selection.anchorNode;
      if (node && node.nodeType === 3) node = node.parentNode;

      const blockTags = ['H2', 'BLOCKQUOTE', 'PRE'];
      let currentBlock = node as Node | null;
      
      while (currentBlock && currentBlock !== editorRef.current) {
        if (currentBlock.nodeName && blockTags.includes(currentBlock.nodeName)) break;
        currentBlock = currentBlock.parentNode;
      }

      if (currentBlock && currentBlock !== editorRef.current && blockTags.includes(currentBlock.nodeName)) {
        e.preventDefault();
        
        const p = document.createElement('p');
        p.innerHTML = '<br>';
        
        if (currentBlock.parentNode) {
          currentBlock.parentNode.insertBefore(p, currentBlock.nextSibling);
        }
        
        const range = document.createRange();
        range.setStart(p, 0);
        range.collapse(true);
        
        selection.removeAllRanges();
        selection.addRange(range);
        
        onChange(editorRef.current.innerHTML);
        updateActiveStates();
      }
    }
  };

  const handleImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || isUploadingImage) return;
    await onImageUpload(file);
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const handleImageButtonClick = (e: React.MouseEvent) => {
    e.preventDefault();
    if (!isUploadingImage) {
      fileInputRef.current?.click();
    }
  };

  const getButtonClass = (isActive: boolean) => 
    `btn btn-sm ${isActive ? 'btn-secondary text-white' : 'btn-outline-secondary'}`;

  return (
    <div className="border rounded">
      <div className="d-flex flex-wrap gap-1 p-2 border-bottom bg-light sticky-top" style={{ zIndex: 10 }}>
        <button type="button" className={getButtonClass(activeStates.bold)} onMouseDown={(e) => handleMouseDown(e, 'bold')} title="Bold"><i className="fas fa-bold"></i></button>
        <button type="button" className={getButtonClass(activeStates.italic)} onMouseDown={(e) => handleMouseDown(e, 'italic')} title="Italic"><i className="fas fa-italic"></i></button>
        <button type="button" className={getButtonClass(activeStates.insertUnorderedList)} onMouseDown={(e) => handleMouseDown(e, 'insertUnorderedList')} title="Bullet List"><i className="fas fa-list-ul"></i></button>
        <button type="button" className={getButtonClass(activeStates.insertOrderedList)} onMouseDown={(e) => handleMouseDown(e, 'insertOrderedList')} title="Numbered List"><i className="fas fa-list-ol"></i></button>
        <button type="button" className={getButtonClass(activeStates.h2)} onMouseDown={(e) => handleMouseDown(e, 'formatBlock', 'h2')} title="Heading"><i className="fas fa-heading"></i></button>
        <button type="button" className={getButtonClass(activeStates.blockquote)} onMouseDown={(e) => handleMouseDown(e, 'formatBlock', 'blockquote')} title="Quote"><i className="fas fa-quote-right"></i></button>
        <button type="button" className={getButtonClass(activeStates.pre)} onMouseDown={(e) => handleMouseDown(e, 'formatBlock', 'pre')} title="Code Block"><i className="fas fa-code"></i></button>
        <button 
          type="button" 
          className={getButtonClass(false)} 
          onMouseDown={(e) => e.preventDefault()} 
          onClick={handleImageButtonClick} 
          title="Insert Image"
          disabled={isUploadingImage}
        >
          {isUploadingImage ? (
            <span className="spinner-border spinner-border-sm" />
          ) : (
            <i className="fas fa-image"></i>
          )}
        </button>
        <input type="file" ref={fileInputRef} className="d-none" accept="image/*" onChange={handleImageUpload} />
      </div>
      <div
        ref={editorRef}
        contentEditable
        suppressContentEditableWarning
        className="p-4 editor-content"
        style={{ minHeight: '500px', outline: 'none' }}
        onInput={(e) => onChange(e.currentTarget.innerHTML)}
        onKeyDown={handleKeyDown}
        onMouseUp={updateActiveStates}
        onKeyUp={updateActiveStates}
        onClick={updateActiveStates}
        onFocus={updateActiveStates}
      />
    </div>
  );
}