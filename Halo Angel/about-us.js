(() => {
  const form = document.querySelector("#about-contact-form");
  const status = document.querySelector("#form-status");

  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    if (status) {
      status.textContent =
        "Your message was not sent: this form is a preview and is not connected to a contact service.";
    }
  });
})();
