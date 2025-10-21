// Modal helper functions - keep behavior explicit and deterministic
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  // Prefer class-based show to avoid inline style conflicts
  modal.classList.add("show");
  modal.style.display = ""; // clear any inline style
  document.body.style.overflow = "hidden"; // Prevent background scrolling while modal open
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.remove("show");
  modal.style.display = ""; // rely on CSS .modal default display:none
  document.body.style.overflow = "auto";
}

// Close modal when clicking on backdrop (but not when clicking inside modal content)
document.addEventListener("click", function (event) {
  if (
    event.target &&
    event.target.classList &&
    event.target.classList.contains("modal")
  ) {
    event.target.classList.remove("show");
    event.target.style.display = "";
    document.body.style.overflow = "auto";
  }
});

// On DOMContentLoaded, ensure modals are hidden by default and do NOT auto-open.
// We intentionally avoid opening modals automatically based on URL params to prevent the "flash" behavior.
document.addEventListener("DOMContentLoaded", function () {
  const login = document.getElementById("loginModal");
  const signup = document.getElementById("signupModal");
  if (login) {
    login.classList.remove("show");
    login.style.display = "";
  }
  if (signup) {
    signup.classList.remove("show");
    signup.style.display = "";
  }
});
