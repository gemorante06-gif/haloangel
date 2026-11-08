(() => {
  const text = (value) => (typeof value === "string" ? value.trim() : "");
  const IMAGE_BY_CAMERA = {
    "sony zv-e10": "image/SONY ZVE10.png",
    "dji osmo pocket 3": "image/POCKET3.png",
    "osmo pocket 3": "image/POCKET3.png",
    "dji osmo action 4": "image/ACTIONPOCKET4.png",
    "osmo action 4": "image/ACTIONPOCKET4.png",
    "insta360 x5": "image/X360.png",
    "fujifilm instax mini evo": "image/MINIEVO.png",
    "fujifilm instax square sq6": "image/SQ6.png",
  };

  const setText = (selector, value) => {
    const element = document.querySelector(selector);
    if (element) element.textContent = text(value);
  };

  const setVisible = (selector, visible) => {
    const element = document.querySelector(selector);
    if (element) element.hidden = !visible;
  };

  function safeUrl(value) {
    const url = text(value);
    if (!url) return "";

    try {
      const parsed = new URL(url, window.location.href);
      return ["http:", "https:", "file:"].includes(parsed.protocol) ? url : "";
    } catch {
      return "";
    }
  }

  function setImage(image, path, alt) {
    const source = safeUrl(path);
    if (!source) {
      image.hidden = true;
      return;
    }

    image.src = source;
    image.alt = alt;
    image.hidden = false;
  }

  function makeHistoryRow(booking) {
    const row = document.createElement("tr");
    [
      booking.camera,
      booking.dates,
      booking.status,
      booking.paymentStatus,
    ].forEach((value) => {
      const cell = document.createElement("td");
      cell.textContent = text(value);
      row.append(cell);
    });
    return row;
  }

  function renderBooking(booking) {
    const hasBooking = Boolean(booking && typeof booking === "object");
    setVisible("[data-booking-content]", hasBooking);
    setVisible("[data-booking-details]", hasBooking);
    setVisible("[data-booking-badge]", hasBooking && Boolean(text(booking.status)));
    setVisible("[data-booking-actions]", hasBooking);
    setVisible("[data-request-link]", hasBooking && Boolean(text(booking.requestUrl)));
    setVisible("[data-change-link]", hasBooking && Boolean(text(booking.changeUrl)));

    if (!hasBooking) {
      setText("[data-booking-heading]", "Your active booking.");
      setText("[data-booking-empty]", "");
      return;
    }

    setText("[data-booking-heading]", "Your active booking.");
    setText("[data-booking-badge]", booking.status);
    setText("[data-booking-id]", booking.id);
    setText("[data-booking-camera]", booking.camera);
    setText("[data-booking-dates]", booking.dates);
    setText("[data-booking-description]", booking.description);
    setText("[data-booking-status]", booking.status);
    setText("[data-booking-approval]", booking.approval);
    setText("[data-booking-pickup]", booking.pickup);

    const bookingImage = document.querySelector("[data-booking-image]");
    if (bookingImage) {
      const imagePath =
        text(booking.image) || IMAGE_BY_CAMERA[text(booking.camera).toLowerCase()];
      setImage(bookingImage, imagePath, text(booking.camera));
    }

    const requestLink = document.querySelector("[data-request-link]");
    if (requestLink && safeUrl(booking.requestUrl)) requestLink.href = safeUrl(booking.requestUrl);
    const changeLink = document.querySelector("[data-change-link]");
    if (changeLink && safeUrl(booking.changeUrl)) changeLink.href = safeUrl(booking.changeUrl);
  }

  function renderPayment(payment) {
    const hasPayment = Boolean(payment && typeof payment === "object");
    setVisible("[data-payment-badge]", hasPayment && Boolean(text(payment.status)));
    setVisible("[data-payment-link]", hasPayment && Boolean(text(payment.detailsUrl)));

    if (!hasPayment) {
      [
        "[data-payment-status]",
        "[data-payment-note]",
        "[data-payment-total]",
        "[data-payment-total-note]",
        "[data-payment-deposit]",
        "[data-payment-deposit-note]",
        "[data-payment-balance]",
        "[data-payment-balance-note]",
      ].forEach((selector) => setText(selector, ""));
      setText("[data-payment-badge]", "");
      setText("[data-payment-progress]", "");
      return;
    }

    setText("[data-payment-status]", payment.status);
    setText("[data-payment-note]", payment.note);
    setText("[data-payment-badge]", payment.status);
    setText("[data-payment-total]", payment.total);
    setText("[data-payment-total-note]", payment.totalNote);
    setText("[data-payment-deposit]", payment.deposit);
    setText("[data-payment-deposit-note]", payment.depositNote);
    setText("[data-payment-balance]", payment.balance);
    setText("[data-payment-balance-note]", payment.balanceNote);

    const progress = document.querySelector("[data-payment-progress]");
    if (progress) {
      const percentage = Number(payment.progress);
      progress.style.width = Number.isFinite(percentage)
        ? `${Math.max(0, Math.min(100, percentage))}%`
        : "0%";
    }

    const paymentLink = document.querySelector("[data-payment-link]");
    if (paymentLink && safeUrl(payment.detailsUrl)) paymentLink.href = safeUrl(payment.detailsUrl);
  }

  function renderHistory(bookings) {
    const body = document.querySelector("[data-history-body]");
    if (!body) return;

    const history = Array.isArray(bookings) ? bookings : [];
    body.replaceChildren();

    if (!history.length) {
      const row = document.createElement("tr");
      const cell = document.createElement("td");
      cell.className = "history-empty";
      cell.colSpan = 4;
      cell.textContent = "";
      row.append(cell);
      body.append(row);
      setVisible("[data-history-link]", false);
      return;
    }

    body.append(...history.map(makeHistoryRow));
    setVisible("[data-history-link]", true);
  }

  function renderAccount(customer, authenticated) {
    const nameInput = document.querySelector("#account-name");
    const emailInput = document.querySelector("#account-email");
    const phoneInput = document.querySelector("#account-phone");
    const canEdit = authenticated && customer.canEdit === true;

    if (nameInput) {
      nameInput.value = text(customer.name);
      nameInput.readOnly = !canEdit;
    }
    if (emailInput) {
      emailInput.value = text(customer.email);
      emailInput.readOnly = !canEdit;
    }
    if (phoneInput) {
      phoneInput.value = text(customer.phone);
      phoneInput.readOnly = !canEdit;
    }

    const saveButton = document.querySelector("[data-save-account]");
    if (saveButton) saveButton.disabled = !canEdit;
    setVisible("[data-sign-out]", authenticated);

    const accountLink = document.querySelector("[data-account-link]");
    if (accountLink) {
      accountLink.href = authenticated ? "#account-settings" : "login.html";
      accountLink.textContent = authenticated ? text(customer.name) || "My account" : "My account";
    }
  }

  function renderDashboard(data) {
    const payload = data && typeof data === "object" ? data : {};
    const customer =
      payload.customer && typeof payload.customer === "object" ? payload.customer : {};
    const authenticated = payload.authenticated === true && Boolean(Object.keys(customer).length);
    const bookings = Array.isArray(payload.bookings)
      ? payload.bookings.filter((booking) => booking && typeof booking === "object")
      : [];
    const activeBooking =
      payload.activeBooking && typeof payload.activeBooking === "object"
        ? payload.activeBooking
        : bookings.find((booking) => text(booking.status).toLowerCase() === "active");
    const summary =
      payload.summary && typeof payload.summary === "object" ? payload.summary : {};
    const verification =
      payload.verification && typeof payload.verification === "object"
        ? payload.verification
        : {};
    const payment =
      payload.payment && typeof payload.payment === "object" ? payload.payment : null;

    setText("[data-welcome-message]", authenticated ? "Welcome back, creator." : "Welcome, creator.");
    setText(
      "[data-welcome-copy]",
      authenticated
        ? "Keep your rental plans, verification, and payment progress in one place. Your next steps are here."
        : "Keep your rental plans, verification, and payment progress in one place.",
    );
    setText("[data-dashboard-note]", "");
    setText("[data-rental-status]", summary.rentalStatus || activeBooking?.status);
    setText("[data-rental-status-note]", summary.rentalNote || activeBooking?.statusNote);
    setText("[data-verification-status]", summary.verificationStatus || verification.status);
    setText("[data-verification-note]", summary.verificationNote || verification.note);
    setText("[data-payment-status]", payment?.status);
    setText("[data-payment-note]", payment?.note);
    setText("[data-booking-empty]", "");
    setText("[data-booking-id]", "");
    setText("[data-booking-camera]", "");
    setText("[data-booking-dates]", "");
    setText("[data-booking-description]", "");
    setText("[data-booking-status]", "");
    setText("[data-booking-approval]", "");
    setText("[data-booking-pickup]", "");

    const verificationLink = document.querySelector("[data-verification-link]");
    if (verificationLink) {
      const verificationUrl = safeUrl(payload.verificationUrl);
      verificationLink.hidden = !verificationUrl;
      if (verificationUrl) verificationLink.href = verificationUrl;
    }

    renderBooking(activeBooking);
    renderPayment(payment);
    renderHistory(bookings);
    renderAccount(customer, authenticated);
  }

  function showLoadError() {
    setText("[data-dashboard-note]", "Account information could not be loaded.");
  }

  async function loadDashboard() {
    const endpoint = text(document.body.dataset.dashboardEndpoint);
    if (!endpoint) {
      renderDashboard(null);
      return;
    }

    try {
      const response = await fetch(endpoint, {
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      });
      if (!response.ok) throw new Error(`Dashboard request failed (${response.status}).`);
      renderDashboard(await response.json());
    } catch (error) {
      console.error("Unable to load dashboard data.", error);
      showLoadError();
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener(
      "error",
      (event) => {
        if (event.target instanceof HTMLImageElement && event.target.matches(".booking-image")) {
          event.target.hidden = true;
        }
      },
      true,
    );

    document.querySelector("[data-account-form]")?.addEventListener("submit", (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      if (!(form instanceof HTMLFormElement) || form.querySelector("[data-save-account]")?.disabled) {
        return;
      }

      window.dispatchEvent(
        new CustomEvent("haloangel:account-save", {
          detail: Object.fromEntries(new FormData(form).entries()),
        }),
      );
    });
    document.querySelector("[data-password-form]")?.addEventListener("submit", (event) => {
      event.preventDefault();
    });
    document.querySelector("[data-sign-out]")?.addEventListener("click", (event) => {
      event.preventDefault();
      window.location.href = "login.html";
    });

    window.HaloAngelDashboard = { render: renderDashboard };
    loadDashboard();
  });
})();
