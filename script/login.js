function togglePassword(id, el) {
  const input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
    el.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
  } else {
    input.type = "password";
    el.innerHTML = '<i class="fa-solid fa-eye"></i>';
  }
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("login.js loaded ✅");
});
