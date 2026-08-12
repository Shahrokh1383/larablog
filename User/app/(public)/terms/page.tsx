import type { Metadata } from "next";
import { TermsView } from "@/features/terms";
import "@/styles/terms.css";

export const metadata: Metadata = {
  title: "Terms & Conditions — LaraBlog",
  description: "Terms and Conditions of using LaraBlog.",
};

export default function TermsPage() {
  return <TermsView />;
}