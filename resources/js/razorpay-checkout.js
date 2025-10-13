/*
  Minimal Razorpay checkout bootstrapper.
  This ensures Vite has a concrete entry and avoids build failures.
  If the Razorpay SDK is needed, this will inject it once on demand.
*/

const RAZORPAY_SDK_URL = "https://checkout.razorpay.com/v1/checkout.js";

function ensureRazorpayScriptLoaded() {
  if (typeof window === "undefined") return Promise.resolve();

  // If already loaded
  if (window.Razorpay) return Promise.resolve();

  // If a loading tag already exists
  const existing = document.querySelector(`script[src="${RAZORPAY_SDK_URL}"]`);
  if (existing) {
    return new Promise((resolve) => {
      existing.addEventListener("load", () => resolve());
      // If it has already loaded
      if (existing.readyState === "complete") resolve();
    });
  }

  // Inject script
  return new Promise((resolve, reject) => {
    const s = document.createElement("script");
    s.src = RAZORPAY_SDK_URL;
    s.async = true;
    s.onload = () => resolve();
    s.onerror = (e) => reject(e);
    document.head.appendChild(s);
  });
}

// Expose a guarded initializer in case pages want to call it
export async function initRazorpay() {
  await ensureRazorpayScriptLoaded();
  return window.Razorpay;
}

// Auto-load only if an element requests it via data attribute
if (typeof window !== "undefined") {
  if (document.querySelector("[data-load-razorpay]") || window.__AUTO_LOAD_RAZORPAY__) {
    ensureRazorpayScriptLoaded().catch(() => {
      // Silently ignore in production if script fails; page can retry manually
    });
  }
}


