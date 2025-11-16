document.addEventListener("DOMContentLoaded", () => {
  const tradeRadios = document.querySelectorAll('input[name="trade"]');
  const priceField = document.getElementById("priceField");

  tradeRadios.forEach(radio => {
    radio.addEventListener("change", () => {
      if (radio.value === "paid") {
        priceField.style.display = "block";
      } else {
        priceField.style.display = "none";
        priceField.querySelector("input").value = "";
      }
    });
  });
});
