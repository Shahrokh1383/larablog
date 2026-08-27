import RichTextEditor from './RichTextEditor';

interface BodyFieldProps {
  value: string;
  onChange: (value: string) => void;
  onImageUpload: (file: File) => Promise<string | undefined>;
  isUploadingImage: boolean;
  error?: string;
}

export default function BodyField({
  value,
  onChange,
  onImageUpload,
  isUploadingImage,
  error,
}: BodyFieldProps) {
  return (
    <div className="mb-3">
      <label className="form-label">Body</label>
      <RichTextEditor
        value={value}
        onChange={onChange}
        onImageUpload={onImageUpload}
        isUploadingImage={isUploadingImage}
      />
      {error && <div className="text-danger small mt-1">{error}</div>}
    </div>
  );
}