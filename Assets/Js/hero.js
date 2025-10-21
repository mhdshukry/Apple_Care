/* Modern Hero Slider JS - progressive enhancement for .slider */
(function () {
  function initSlider(root) {
    const slides = Array.from(root.querySelectorAll(".slide"));
    if (!slides.length) return;

    // Inject UI: arrows, dots, progress
    const nav = document.createElement("div");
    nav.className = "hero-nav";
    nav.innerHTML = `\n      <button class="arrow prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>\n      <button class="arrow next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>\n    `;
    const pagination = document.createElement("div");
    pagination.className = "hero-pagination";
    pagination.innerHTML = slides
      .map(
        (_, i) =>
          `<button class="dot${
            i === 0 ? " active" : ""
          }" aria-label="Go to slide ${i + 1}"></button>`
      )
      .join("");
    root.appendChild(nav);
    root.appendChild(pagination);
    // No progress bar per request

    const prevBtn = nav.querySelector(".prev");
    const nextBtn = nav.querySelector(".next");
    const dots = Array.from(pagination.querySelectorAll(".dot"));

    let index = Math.max(
      0,
      slides.findIndex((s) => s.classList.contains("active"))
    );
    let autoplayMs = 5000;
    let timerId = null;
    // let startedAt = 0; // Removed as progress bar logic is eliminated

    function setActive(i) {
      slides.forEach((s, idx) => s.classList.toggle("active", idx === i));
      dots.forEach((d, idx) => d.classList.toggle("active", idx === i));
      index = i;
      restartAutoplay();
    }

    function next() {
      setActive((index + 1) % slides.length);
    }
    function prev() {
      setActive((index - 1 + slides.length) % slides.length);
    }

    function restartAutoplay() {
      if (timerId) clearTimeout(timerId);
      timerId = setTimeout(next, autoplayMs);
    }

    // Events
    nextBtn.addEventListener("click", () => {
      next();
    });
    prevBtn.addEventListener("click", () => {
      prev();
    });
    dots.forEach((d, i) => d.addEventListener("click", () => setActive(i)));

    // Pause on hover/focus within
    let paused = false;
    function pause() {
      if (paused) return;
      paused = true;
      if (timerId) {
        clearTimeout(timerId);
        timerId = null;
      }
    }
    function resume() {
      if (!paused) return;
      paused = false;
      restartAutoplay();
    }
    root.addEventListener("mouseenter", pause);
    root.addEventListener("mouseleave", resume);
    root.addEventListener("focusin", pause);
    root.addEventListener("focusout", resume);

    // Swipe/drag support (touch + mouse)
    let startX = 0,
      startY = 0,
      moved = false,
      dragging = false;
    root.addEventListener(
      "touchstart",
      (e) => {
        if (!e.touches || !e.touches[0]) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        moved = false;
        dragging = true;
        pause();
      },
      { passive: true }
    );
    root.addEventListener(
      "touchmove",
      (e) => {
        moved = true; // we don't do dragging visuals here to keep simple
      },
      { passive: true }
    );
    root.addEventListener("touchend", (e) => {
      const endX =
        e.changedTouches && e.changedTouches[0]
          ? e.changedTouches[0].clientX
          : startX;
      const dx = endX - startX;
      if (Math.abs(dx) > 30 && moved) {
        if (dx < 0) next();
        else prev();
      }
      dragging = false;
      resume();
    });

    // Mouse dragging
    root.addEventListener("mousedown", (e) => {
      startX = e.clientX;
      startY = e.clientY;
      moved = false;
      dragging = true;
      pause();
    });
    window.addEventListener("mousemove", (e) => {
      if (!dragging) return;
      moved = true;
    });
    window.addEventListener("mouseup", (e) => {
      if (!dragging) return;
      dragging = false;
      const dx = e.clientX - startX;
      if (Math.abs(dx) > 30 && moved) {
        if (dx < 0) next();
        else prev();
      }
      resume();
    });

    // Keyboard
    root.setAttribute("tabindex", "0");
    root.addEventListener("keydown", (e) => {
      if (e.key === "ArrowRight") {
        e.preventDefault();
        next();
      } else if (e.key === "ArrowLeft") {
        e.preventDefault();
        prev();
      }
    });

    // Init
    setActive(index);
  }

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  ready(() => {
    document.querySelectorAll(".slider").forEach(initSlider);
  });
})();
