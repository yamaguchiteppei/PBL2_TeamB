document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ verify.js loaded");
  const box = document.querySelector(".verify-box");
  if (box) box.classList.add("loaded");
});
