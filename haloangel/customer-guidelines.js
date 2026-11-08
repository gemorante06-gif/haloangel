(() => {
  const guides = {
    sony: {
      name: "Sony ZV-E10",
      image: "image/SONY ZVE10.png",
      alt: "Sony ZV-E10 camera with microphone",
    },
    pocket: {
      name: "DJI Osmo Pocket 3",
      image: "image/POCKET3.png",
      alt: "DJI Osmo Pocket 3 camera",
    },
    action: {
      name: "DJI Osmo Action 4",
      image: "image/ACTIONPOCKET4.png",
      alt: "DJI Osmo Action 4 camera",
    },
    insta360: {
      name: "Insta360 X5",
      image: "image/X360.png",
      alt: "Insta360 X5 camera",
    },
    instax: {
      name: "Instax Mini Evo / Instax SQ6",
      image: "image/MINIEVO.png",
      alt: "Instax camera",
    },
  };

  function showGuide(model) {
    const guide = guides[model];
    if (!guide) return;

    const title = document.querySelector("[data-camera-name]");
    const image = document.querySelector("[data-camera-image]");
    if (title) title.textContent = guide.name;
    if (image) {
      image.src = guide.image;
      image.alt = guide.alt;
      image.hidden = false;
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    const selector = document.querySelector("#camera-model");
    selector?.addEventListener("change", () => showGuide(selector.value));

    document.addEventListener(
      "error",
      (event) => {
        if (event.target instanceof HTMLImageElement && event.target.matches("[data-camera-image]")) {
          event.target.hidden = true;
        }
      },
      true,
    );
  });
})();
