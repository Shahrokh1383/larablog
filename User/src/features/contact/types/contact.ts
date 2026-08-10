export interface ContactPayload {
  name?: string;
  email?: string;
  subject: string;
  message: string;
}

export interface ContactResponse {
  message: string;
}

export interface ContactFormData {
  name: string;
  email: string;
  subject: string;
  message: string;
}

export interface FaqItem {
  question: string;
  answer: string;
}