try {
  document
    .querySelectorAll(".sidebar ul li a:not(.social-media-link)")
    .forEach(function (link) {
      link.addEventListener("click", function () {
        var active = document.querySelector(".sidebar ul li.active");
        if (active) active.classList.remove("active");
        if (this.parentElement) this.parentElement.classList.add("active");
      });
    });
} catch (e) {
  /* ignore */
}

try {
  var fileIn = document.getElementById("image_url");
  if (fileIn) {
    fileIn.addEventListener("change", function () {
      var name =
        this.files && this.files[0] && this.files[0].name
          ? this.files[0].name
          : "";
      var out = document.getElementById("file-upload-filename");
      if (out) out.textContent = name;
    });
  }
} catch (e) {
  /* ignore */
}
