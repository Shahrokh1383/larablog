import type { Metadata } from "next";
import { PrivacyView } from "@/features/privacy";
import "@/styles/privacy.css";
export const metadata: Metadata = {
  title: "Privacy Policy — LaraBlog",
  description: "Privacy Policy of LaraBlog — how we handle your data.",
};

export default function PrivacyPage() {
  return <PrivacyView />;
}