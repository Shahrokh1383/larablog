import Link from "next/link";
import { TermsHeader } from "./TermsHeader";
import { TermsCard } from "./TermsCard";

const termsData = [
  {
    title: "1. Acceptance of Terms",
    paragraphs: [
      "By accessing or using LaraBlog, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you must not use our services.",
      "We reserve the right to update these terms at any time. Continued use of the platform after changes constitutes acceptance of the new terms.",
    ],
  },
  {
    title: "2. User Accounts",
    paragraphs: [
      "When you create an account, you are responsible for maintaining the confidentiality of your credentials and for all activities that occur under your account. You must provide accurate, current, and complete information during registration.",
      "LaraBlog reserves the right to suspend or terminate accounts that violate our policies or engage in fraudulent, abusive, or illegal activity.",
    ],
  },
  {
    title: "3. Content & Copyright",
    paragraphs: [
      "All articles, images, graphics, and other content published on LaraBlog are the intellectual property of their respective authors and are protected by copyright law. You may not reproduce, distribute, or create derivative works without explicit permission.",
      "By submitting content to the platform, you grant LaraBlog a non-exclusive, royalty-free license to display, modify, and distribute your content on the site and affiliated channels.",
    ],
  },
  {
    title: "4. User Conduct",
    paragraphs: [
      "Users agree not to post any content that is unlawful, defamatory, harassing, or invasive of privacy. Hate speech, spam, and malicious software are strictly prohibited.",
      "We encourage respectful discourse and reserve the right to remove any content or comments that violate these guidelines without prior notice.",
    ],
  },
  {
    title: "5. Third-Party Links & Services",
    paragraphs: [
      "LaraBlog may contain links to external websites or services. We do not endorse or assume responsibility for the content, privacy policies, or practices of any third-party sites. Access them at your own risk.",
    ],
  },
  {
    title: "6. Disclaimer of Warranties",
    paragraphs: [
      'The platform is provided "as is" and "as available" without warranties of any kind, either express or implied. LaraBlog does not guarantee the accuracy, completeness, or timeliness of the content.',
    ],
  },
  {
    title: "7. Limitation of Liability",
    paragraphs: [
      "To the fullest extent permitted by law, LaraBlog and its team shall not be liable for any indirect, incidental, special, or consequential damages arising out of or in connection with your use of the platform.",
    ],
  },
  {
    title: "8. Contact Information",
    paragraphs: [
      <>
        If you have any questions about these Terms, please contact us through our{" "}
        <Link href="/contact">contact page</Link>.
      </>,
    ],
  },
];

export function TermsView() {
  return (
    <main>
      <section className="terms-section section-padding">
        <div className="container">
          <TermsHeader />
          <div className="terms-content">
            {termsData.map((term) => (
              <TermsCard key={term.title} title={term.title}>
                {term.paragraphs.map((paragraph, index) => (
                  <p key={index}>{paragraph}</p>
                ))}
              </TermsCard>
            ))}
          </div>
        </div>
      </section>
    </main>
  );
}