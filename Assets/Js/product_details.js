// Handle storage radio inputs (they are rendered as <input type="radio" name="storage" ...>)
document.addEventListener("DOMContentLoaded", () => {
  const storageRadios = document.querySelectorAll('input[name="storage"]');
  const priceEl = document.getElementById("price");
  const maxQtyEl = document.getElementById("max-quantity");

  storageRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      const storageId = this.value;
      const price = this.getAttribute("data-price");
      const quantityAvailable = this.getAttribute("data-quantity");

      // Update price display
      if (priceEl)
        priceEl.innerText = "Price: Rs" + parseFloat(price).toFixed(2);

      // Update max quantity display
      if (maxQtyEl) maxQtyEl.innerText = "Max Quantity: " + quantityAvailable;

      // Update hidden inputs in forms
      const cartStorage = document.getElementById("cart-storage-id");
      const buyStorage = document.getElementById("buy-storage-id");
      if (cartStorage) cartStorage.value = storageId;
      if (buyStorage) buyStorage.value = storageId;
    });
  });

  // Initialize values from the checked radio on page load
  const checked = document.querySelector('input[name="storage"]:checked');
  if (checked) {
    checked.dispatchEvent(new Event("change"));
  }

  // Quantity controls - keep the hidden inputs in sync
  const quantityInput = document.getElementById("quantity");
  const cartQty = document.getElementById("cart-quantity");
  const buyQty = document.getElementById("buy-quantity");
  if (quantityInput) {
    quantityInput.addEventListener("input", function () {
      const v = Math.max(1, parseInt(this.value) || 1);
      if (cartQty) cartQty.value = v;
      if (buyQty) buyQty.value = v;
    });
  }

  // Color buttons
  const colorButtons = document.querySelectorAll(".color-button");
  const clearColorButton = document.getElementById("clear-color");
  const cartColor = document.getElementById("cart-color");
  const buyColor = document.getElementById("buy-color");

  colorButtons.forEach((button) => {
    button.addEventListener("click", () => {
      colorButtons.forEach((btn) => btn.classList.remove("checked"));
      button.classList.add("checked");
      const selectedColor = button.getAttribute("data-color-name");
      if (cartColor) cartColor.value = selectedColor;
      if (buyColor) buyColor.value = selectedColor;
    });
  });

  if (clearColorButton) {
    clearColorButton.addEventListener("click", (e) => {
      e.preventDefault();
      colorButtons.forEach((btn) => btn.classList.remove("checked"));
      if (cartColor) cartColor.value = "";
      if (buyColor) buyColor.value = "";
    });
  }
});

// the old color selection logic is merged above with form wiring

document.addEventListener("DOMContentLoaded", () => {
  const stars = document.querySelectorAll(".star");
  let selectedRating = 0;

  stars.forEach((star) => {
    star.addEventListener("click", () => {
      selectedRating = parseInt(star.getAttribute("data-rating"));
      stars.forEach((s) => {
        s.classList.toggle(
          "checked",
          parseInt(s.getAttribute("data-rating")) <= selectedRating
        );
      });
      document.querySelector('input[name="rating"]').value = selectedRating;
    });
  });

  // Set hidden input for rating
  const ratingInput = document.createElement("input");
  ratingInput.type = "hidden";
  ratingInput.name = "rating";
  ratingInput.value = "0";
  document.querySelector(".review-form").appendChild(ratingInput);
});
