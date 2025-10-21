(function () {
  // Theme toggle with localStorage
  var btn = document.getElementById("themeToggle");
  var root = document.documentElement;
  var key = "admin-theme";
  function apply(theme) {
    if (theme === "dark") {
      root.setAttribute("data-theme", "dark");
    } else {
      root.removeAttribute("data-theme");
    }
  }
  try {
    var saved = localStorage.getItem(key);
    if (saved) {
      apply(saved);
    }
  } catch (e) {
    /* ignore */
  }
  if (btn) {
    btn.addEventListener("click", function () {
      var isDark = root.getAttribute("data-theme") === "dark";
      var next = isDark ? "light" : "dark";
      apply(next);
      try {
        localStorage.setItem(key, next);
      } catch (e) {
        /* ignore */
      }
      // swap icon
      var i = btn.querySelector("i");
      if (i) {
        i.className = next === "dark" ? "far fa-sun" : "far fa-moon";
      }
    });
  }
})();

// Global layout helper: keep main content aligned with the actual sidebar width
(function () {
  function applySidebarWidth() {
    try {
      var sb = document.querySelector(".sidebar");
      if (!sb) return;
      // Use computed CSS width (content width) to avoid including padding/border
      var cs = window.getComputedStyle(sb);
      var w = parseFloat(cs.width) || 200;
      document.documentElement.style.setProperty("--sidebar-offset", w + "px");
    } catch (e) {
      /* ignore */
    }
  }
  applySidebarWidth();
  window.addEventListener("resize", applySidebarWidth);
  if (window.ResizeObserver) {
    try {
      var sb = document.querySelector(".sidebar");
      if (sb) new ResizeObserver(applySidebarWidth).observe(sb);
    } catch (e) {
      /* ignore */
    }
  }
  // Re-measure after fonts/icons load
  setTimeout(applySidebarWidth, 300);
})();
