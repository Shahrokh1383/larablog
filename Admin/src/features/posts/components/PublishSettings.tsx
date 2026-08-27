interface PublishSettingsProps {
  isPublished: boolean;
  onPublishedChange: (value: boolean) => void;
  isEditorsPick: boolean;
  onEditorsPickChange: (value: boolean) => void;
}

export default function PublishSettings({
  isPublished,
  onPublishedChange,
  isEditorsPick,
  onEditorsPickChange,
}: PublishSettingsProps) {
  return (
    <div className="col-md-4">
      <div className="form-check mt-4">
        <input
          type="checkbox"
          className="form-check-input"
          id="is_published"
          checked={isPublished}
          onChange={(e) => onPublishedChange(e.target.checked)}
        />
        <label className="form-check-label" htmlFor="is_published">
          Publish immediately
        </label>
      </div>
      <div className="form-check mt-2">
        <input
          type="checkbox"
          className="form-check-input"
          id="is_editors_pick"
          checked={isEditorsPick}
          onChange={(e) => onEditorsPickChange(e.target.checked)}
        />
        <label className="form-check-label" htmlFor="is_editors_pick">
          Editor's Pick
        </label>
      </div>
    </div>
  );
}