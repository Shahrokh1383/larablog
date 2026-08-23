const DANGEROUS_TAGS = /(&lt;|<)\/?(script|iframe|object|embed|link|style)(&gt;|>)/gi;
const EVENT_HANDLERS = /\s*on\w+\s*=\s*(?:"[^"]*"|'[^']*'|[^\s>]+)/gi;

export function sanitizeHtml(html: string): string {
  if (!html) return '';
  return html
    .replace(DANGEROUS_TAGS, '')
    .replace(EVENT_HANDLERS, '')
    .replace(/javascript:/gi, '');
}