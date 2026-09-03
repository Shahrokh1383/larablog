export interface Subscriber {
  id: string;
  email: string;
  is_active: boolean;
  created_at: string;
}

export interface ContactMessage {
  id: string;
  subject: string;
  message: string;
  is_read: boolean;
  replied_at: string | null;
  author: { id?: string; name: string; email?: string };
  created_at: string;
}

export interface SendNewsletterPayload {
  send_to_all: boolean;
  subscriber_ids?: string[];
}