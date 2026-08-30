import { useRef, useEffect, useState, useCallback } from 'react';

interface UseRichTextEditorProps {
  value: string;
  onChange: (html: string) => void;
  onImageUpload: (file: File) => Promise<string | undefined>;
  isUploadingImage: boolean;
}

export function useRichTextEditor({ value, onChange, onImageUpload, isUploadingImage }: UseRichTextEditorProps) {
  const editorRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const savedRangeRef = useRef<Range | null>(null);
  const [activeStates, setActiveStates] = useState<Record<string, boolean>>({});

  // Set initial content
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

    // Save the current range for later use (e.g., image insertion)
    savedRangeRef.current = selection.getRangeAt(0).cloneRange();

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

  const getClosestBlock = (tag: string): HTMLElement | null => {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0 || !editorRef.current) return null;

    let node = selection.anchorNode;
    if (node && node.nodeType === 3) node = node.parentNode;

    while (node && node !== editorRef.current) {
      if (node.nodeName?.toLowerCase() === tag.toLowerCase()) {
        return node as HTMLElement;
      }
      node = node.parentNode;
    }
    return null;
  };

  const unwrapBlock = (block: HTMLElement) => {
    const parent = block.parentNode;
    if (!parent) return;

    let newElement: HTMLElement | null = null;

    if (block.tagName === 'BLOCKQUOTE') {
      // Move all child nodes before the blockquote, then remove blockquote
      const firstChild = block.firstChild;
      while (block.firstChild) {
        parent.insertBefore(block.firstChild, block);
      }
      parent.removeChild(block);
      newElement = firstChild as HTMLElement | null;
    } else {
      // For H2, PRE: replace with <p> and move children
      const p = document.createElement('p');
      while (block.firstChild) {
        p.appendChild(block.firstChild);
      }
      if (p.innerHTML === '') {
        p.innerHTML = '<br>';
      }
      parent.replaceChild(p, block);
      newElement = p;
    }

    if (newElement) {
      const range = document.createRange();
      range.setStart(newElement, 0);
      range.collapse(true);
      const selection = window.getSelection();
      selection?.removeAllRanges();
      selection?.addRange(range);
    }
  };

  const toggleBlockFormat = (tag: string) => {
    const currentBlock = getClosestBlock(tag);
    if (currentBlock) {
      unwrapBlock(currentBlock);
    } else {
      document.execCommand('formatBlock', false, tag);
    }
  };

  const exec = (command: string, val?: string) => {
    if (command === 'formatBlock' && val) {
      toggleBlockFormat(val);
    } else {
      document.execCommand(command, false, val);
    }
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
    if (e.key !== 'Enter' || e.shiftKey) return;

    const selection = window.getSelection();
    if (!selection || !selection.rangeCount || !editorRef.current) return;

    let node = selection.anchorNode;
    if (node && node.nodeType === 3) node = node.parentNode;

    const blockTags = ['H2', 'BLOCKQUOTE', 'PRE'];
    let currentBlock = node as Node | null;

    while (currentBlock && currentBlock !== editorRef.current) {
      if (currentBlock.nodeName && blockTags.includes(currentBlock.nodeName)) {
        break;
      }
      currentBlock = currentBlock.parentNode;
    }

    if (
      currentBlock &&
      currentBlock !== editorRef.current &&
      blockTags.includes(currentBlock.nodeName)
    ) {
      e.preventDefault();

      const br = document.createElement('br');
      const range = selection.getRangeAt(0);
      range.deleteContents();
      range.insertNode(br);
      range.setStartAfter(br);
      range.collapse(true);
      selection.removeAllRanges();
      selection.addRange(range);

      onChange(editorRef.current.innerHTML);
      updateActiveStates();
    }
  };

  const handleImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || isUploadingImage) return;

    try {
      const url = await onImageUpload(file);
      if (url && editorRef.current) {
        const img = document.createElement('img');
        img.src = url;
        img.className = 'img-fluid';
        img.alt = 'Article body image';

        const range = savedRangeRef.current;
        if (range && editorRef.current.contains(range.commonAncestorContainer)) {
          range.deleteContents();
          range.insertNode(img);
          range.setStartAfter(img);
          range.collapse(true);
          const selection = window.getSelection();
          selection?.removeAllRanges();
          selection?.addRange(range);
        } else {
          const endRange = document.createRange();
          endRange.selectNodeContents(editorRef.current);
          endRange.collapse(false);
          endRange.insertNode(img);
        }

        onChange(editorRef.current.innerHTML);
        updateActiveStates();
      }
    } catch (error) {
      console.error('Image upload failed:', error);
    } finally {
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const handleImageButtonClick = (e: React.MouseEvent) => {
    e.preventDefault();
    if (!isUploadingImage) {
      fileInputRef.current?.click();
    }
  };

  const handleInput = (e: React.FormEvent<HTMLDivElement>) => {
    onChange(e.currentTarget.innerHTML);
  };

  return {
    editorRef,
    fileInputRef,
    activeStates,
    handleMouseDown,
    handleKeyDown,
    handleImageUpload,
    handleImageButtonClick,
    handleInput,
    updateActiveStates,
  };
}