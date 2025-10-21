// Auth modal enhancements: toggle password visibility, switch between forms, basic validation
(function () {
  function qs(s, r = document) {
    return r.querySelector(s);
  }
  function qsa(s, r = document) {
    return Array.from(r.querySelectorAll(s));
  }

  function open(id) {
    const el = qs("#" + id);
    if (!el) return;
    el.classList.add("show");
  }
  function close(id) {
    const el = qs("#" + id);
    if (!el) return;
    el.classList.remove("show");
  }

  window.openModal = open; // keep compatibility with existing buttons
  window.closeModal = close;

  document.addEventListener("click", (e) => {
    // backdrop close
    const modal = e.target.closest(".modal");
    if (modal && e.target === modal) modal.classList.remove("show");
  });

  // Toggle password visibility
  qsa("[data-toggle-pass]").forEach((t) => {
    t.addEventListener("click", () => {
      const inp = t.closest(".input-wrap").querySelector("input");
      if (!inp) return;
      inp.type = inp.type === "password" ? "text" : "password";
      t.classList.toggle("on");
    });
  });

  // Switch modals
  qsa("[data-open-modal]").forEach((a) => {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      const target = a.getAttribute("data-open-modal");
      const parentModal = a.closest(".modal");
      if (parentModal) parentModal.classList.remove("show");
      open(target);
    });
  });
})();
