interface EditorToolbarProps {
  activeStates: Record<string, boolean>;
  onCommand: (e: React.MouseEvent, command: string, val?: string) => void;
  onImageButtonClick: (e: React.MouseEvent) => void;
  isUploadingImage: boolean;
  fileInputRef: React.RefObject<HTMLInputElement | null>;
  onImageUpload: (e: React.ChangeEvent<HTMLInputElement>) => void;
}

export default function EditorToolbar({
  activeStates,
  onCommand,
  onImageButtonClick,
  isUploadingImage,
  fileInputRef,
  onImageUpload,
}: EditorToolbarProps) {
  const getButtonClass = (isActive: boolean) =>
    `btn btn-sm ${isActive ? 'btn-secondary text-white' : 'btn-outline-secondary'}`;

  return (
    <div className="d-flex flex-wrap gap-1 p-2 border-bottom bg-light sticky-top" style={{ zIndex: 10 }}>
      <button
        type="button"
        className={getButtonClass(activeStates.bold)}
        onMouseDown={(e) => onCommand(e, 'bold')}
        title="Bold"
      >
        <i className="fas fa-bold"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(activeStates.italic)}
        onMouseDown={(e) => onCommand(e, 'italic')}
        title="Italic"
      >
        <i className="fas fa-italic"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(activeStates.insertUnorderedList)}
        onMouseDown={(e) => onCommand(e, 'insertUnorderedList')}
        title="Bullet List"
      >
        <i className="fas fa-list-ul"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(activeStates.insertOrderedList)}
        onMouseDown={(e) => onCommand(e, 'insertOrderedList')}
        title="Numbered List"
      >
        <i className="fas fa-list-ol"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(activeStates.h2)}
        onMouseDown={(e) => onCommand(e, 'formatBlock', 'h2')}
        title="Heading"
      >
        <i className="fas fa-heading"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(activeStates.blockquote)}
        onMouseDown={(e) => onCommand(e, 'formatBlock', 'blockquote')}
        title="Quote"
      >
        <i className="fas fa-quote-right"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(activeStates.pre)}
        onMouseDown={(e) => onCommand(e, 'formatBlock', 'pre')}
        title="Code Block"
      >
        <i className="fas fa-code"></i>
      </button>
      <button
        type="button"
        className={getButtonClass(false)}
        onMouseDown={(e) => e.preventDefault()}
        onClick={onImageButtonClick}
        title="Insert Image"
        disabled={isUploadingImage}
      >
        {isUploadingImage ? (
          <span className="spinner-border spinner-border-sm" />
        ) : (
          <i className="fas fa-image"></i>
        )}
      </button>
      <input
        type="file"
        ref={fileInputRef}
        className="d-none"
        accept="image/*"
        onChange={onImageUpload}
      />
    </div>
  );
}