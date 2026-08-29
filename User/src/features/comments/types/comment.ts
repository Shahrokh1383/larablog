export interface CommentAuthor {
  id?: string;
  name: string;
  avatar?: string | null;
}

export interface Comment {
  id: string;
  post_id: string;
  parent_id: string | null;
  body: string;
  is_approved: boolean;
  author: CommentAuthor;
  replies?: Comment[];
  replies_count?: number;
  replies_has_more?: boolean;
  created_at: string;
}

export interface CreateCommentPayload {
  post_id: string;
  body: string;
  parent_id?: string | null;
  name?: string;
  email?: string;
}