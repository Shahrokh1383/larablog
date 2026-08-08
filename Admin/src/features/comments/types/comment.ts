export interface Comment {
  id: string;
  post_id: string;
  parent_id: string | null;
  body: string;
  is_approved: boolean;
  author: {
    id: string;
    name: string;
  };
  created_at: string;
}