import Link from "next/link";
import { PrivacyHeader } from "./PrivacyHeader";
import { PrivacyCard } from "./PrivacyCard";

const privacyData = [
  {
    title: "1. Information We Collect",
    paragraphs: [
      "We may collect personal information you provide directly, such as your name, email address, and profile details when you register an account. Additionally, we automatically collect certain data like IP address, browser type, and pages visited through cookies and similar technologies.",
    ],
  },
  {
    title: "2. How We Use Your Information",
    paragraphs: [
      "Your information helps us personalize your experience, improve our platform, communicate with you about updates or support, and ensure the security of our services. We do not sell your personal data to third parties.",
    ],
  },
  {
    title: "3. Cookies & Tracking",
    paragraphs: [
      "We use essential cookies for site functionality and optional analytics cookies to understand how our site is used. You can control cookie preferences through your browser settings. Disabling certain cookies may affect your experience.",
    ],
  },
  {
    title: "4. Data Sharing",
    paragraphs: [
      "We may share information with trusted service providers who assist us in operating the website (e.g., hosting, email delivery). These parties are obligated to keep your data confidential. We may also disclose information if required by law.",
    ],
  },
  {
    title: "5. Data Security",
    paragraphs: [
      "We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, or destruction. However, no method of transmission over the Internet is 100% secure.",
    ],
  },
  {
    title: "6. Your Rights",
    paragraphs: [
      "You have the right to access, update, or delete your personal information. You may also object to processing or withdraw consent where applicable. To exercise these rights, please contact us at the email below.",
    ],
  },
  {
    title: "7. Third-Party Links",
    paragraphs: [
      "Our site may contain links to external websites. We are not responsible for the privacy practices of those sites. We encourage you to review their policies before providing any personal information.",
    ],
  },
  {
    title: "8. Contact Us",
    paragraphs: [
      <>
        If you have questions about this Privacy Policy, please visit our{" "}
        <Link href="/contact">contact page</Link>.
      </>,
    ],
  },
];

export function PrivacyView() {
  return (
    <main>
      <section className="privacy-section section-padding">
        <div className="container">
          <PrivacyHeader />
          <div className="privacy-content">
            {privacyData.map((item) => (
              <PrivacyCard key={item.title} title={item.title}>
                {item.paragraphs.map((paragraph, index) => (
                  <p key={index}>{paragraph}</p>
                ))}
              </PrivacyCard>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}