let currentImageIndex = -1;
let allImages = [];

document.addEventListener("DOMContentLoaded", () => {
  allImages = Array.from(document.querySelectorAll(".thumbnail")).map(
    (img) => img.src
  );

  document.querySelectorAll(".thumbnail").forEach((img, index) => {
    img.addEventListener("click", () => {
      currentImageIndex = index;
      openLightbox(img.src);
    });
  });

  document.getElementById("closeBtn").addEventListener("click", () => {
    document.getElementById("lightbox").style.display = "none";
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      document.getElementById("lightbox").style.display = "none";
    }
  });
});

function openLightbox(src) {
  const lightbox = document.getElementById("lightbox");
  const lightboxImg = document.getElementById("lightboxImg");
  lightboxImg.src = src;
  lightbox.style.display = "flex";
}

function navigateImage(direction) {
  if (currentImageIndex === -1) return;
  currentImageIndex += direction;

  if (currentImageIndex < 0) {
    currentImageIndex = allImages.length - 1;
  } else if (currentImageIndex >= allImages.length) {
    currentImageIndex = 0;
  }

  const nextSrc = allImages[currentImageIndex];
  const lightboxImg = document.getElementById("lightboxImg");
  lightboxImg.classList.add("fade");
  setTimeout(() => {
    lightboxImg.src = nextSrc;
    lightboxImg.classList.remove("fade");
  }, 150);
}

function toggleDarkMode() {
  document.body.classList.toggle("dark-mode");
}
