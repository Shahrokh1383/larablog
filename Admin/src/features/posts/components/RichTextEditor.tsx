import { useRichTextEditor } from '../hooks/useRichTextEditor';
import EditorToolbar from './EditorToolbar';

interface RichTextEditorProps {
  value: string;
  onChange: (html: string) => void;
  onImageUpload: (file: File) => Promise<string | undefined>;
  isUploadingImage: boolean;
}

export default function RichTextEditor({
  value,
  onChange,
  onImageUpload,
  isUploadingImage,
}: RichTextEditorProps) {
  const {
    editorRef,
    fileInputRef,
    activeStates,
    handleMouseDown,
    handleKeyDown,
    handleImageUpload,
    handleImageButtonClick,
    handleInput,
    updateActiveStates,
  } = useRichTextEditor({ value, onChange, onImageUpload, isUploadingImage });

  return (
    <div className="border rounded">
      <EditorToolbar
        activeStates={activeStates}
        onCommand={handleMouseDown}
        onImageButtonClick={handleImageButtonClick}
        isUploadingImage={isUploadingImage}
        fileInputRef={fileInputRef}
        onImageUpload={handleImageUpload}
      />
      <div
        ref={editorRef}
        contentEditable
        suppressContentEditableWarning
        className="p-4 editor-content"
        style={{ minHeight: '500px', outline: 'none' }}
        onInput={handleInput}
        onKeyDown={handleKeyDown}
        onMouseUp={updateActiveStates}
        onKeyUp={updateActiveStates}
        onClick={updateActiveStates}
        onFocus={updateActiveStates}
      />
    </div>
  );
}