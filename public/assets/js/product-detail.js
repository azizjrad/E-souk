// Quantity selector
document.getElementById("decrease-qty").addEventListener("click", function () {
  var input = document.getElementById("quantity");
  var value = parseInt(input.value);
  if (value > 1) {
    input.value = value - 1;
  }
});

document.getElementById("increase-qty").addEventListener("click", function () {
  var input = document.getElementById("quantity");
  var value = parseInt(input.value);
  var max = parseInt(input.getAttribute("max"));
  if (value < max) {
    input.value = value + 1;
  }
});

// Toggle accordion content
document
  .querySelectorAll(".product-info-section h5, .product-tags-section h5")
  .forEach(function (element) {
    element.addEventListener("click", function () {
      var targetId = this.getAttribute("data-target");
      var content = document.querySelector(targetId);
      content.classList.toggle("show");

      // Toggle icon
      var icon = this.querySelector(".toggle-icon");
      if (icon.classList.contains("fa-chevron-down")) {
        icon.classList.remove("fa-chevron-down");
        icon.classList.add("fa-chevron-up");
      } else {
        icon.classList.remove("fa-chevron-up");
        icon.classList.add("fa-chevron-down");
      }
    });
  });

// Enhanced Product carousel with auto-sliding
document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.querySelector(".product-carousel");
  const prevBtn = document.querySelector(".prev-arrow");
  const nextBtn = document.querySelector(".next-arrow");

  // Calculate card width dynamically - including margin
  const cards = document.querySelectorAll(".product-card");
  if (cards.length === 0) return;

  const cardStyle = window.getComputedStyle(cards[0]);
  const cardWidth =
    cards[0].offsetWidth +
    parseInt(cardStyle.marginLeft) +
    parseInt(cardStyle.marginRight);

  // Calculate how many cards to show based on container width
  const carouselContainer = document.querySelector(
    ".product-carousel-container"
  );
  const visibleWidth = carouselContainer.clientWidth;
  const cardsPerView = Math.floor(visibleWidth / cardWidth);

  let position = 0;
  let maxPosition = (cards.length - cardsPerView) * cardWidth;
  if (maxPosition < 0) maxPosition = 0;

  // Auto-slide variables
  let autoSlideTimer;
  const autoSlideInterval = 5000; // 5 seconds
  let direction = 1; // 1 for right, -1 for left

  // Handle next button click
  nextBtn.addEventListener("click", function () {
    moveToNextPosition();
    resetAutoSlide();
  });

  // Handle prev button click
  prevBtn.addEventListener("click", function () {
    moveToPrevPosition();
    resetAutoSlide();
  });

  // Function to move to next position
  function moveToNextPosition() {
    if (position >= maxPosition) {
      // If we're at the end, go back to start (loop)
      position = 0;
    } else {
      position = Math.min(position + cardWidth, maxPosition);
    }
    updateCarouselPosition();
  }

  // Function to move to previous position
  function moveToPrevPosition() {
    if (position <= 0) {
      // If we're at the start, go to end (loop)
      position = maxPosition;
    } else {
      position = Math.max(position - cardWidth, 0);
    }
    updateCarouselPosition();
  }

  // Update carousel position
  function updateCarouselPosition() {
    carousel.style.transform = `translateX(-${position}px)`;
    updateArrowStatus();
  }

  // Update arrow status based on position
  function updateArrowStatus() {
    // For looping carousel, arrows are always enabled
    prevBtn.classList.remove("disabled");
    nextBtn.classList.remove("disabled");
  }

  // Function to move slide in current direction
  function moveSlide() {
    if (direction === 1) {
      moveToNextPosition();
      // Change direction if we reached the end
      if (position >= maxPosition) {
        direction = -1;
      }
    } else {
      moveToPrevPosition();
      // Change direction if we reached the start
      if (position <= 0) {
        direction = 1;
      }
    }
  }

  // Start auto-sliding
  function startAutoSlide() {
    if (autoSlideTimer) {
      clearInterval(autoSlideTimer);
    }
    autoSlideTimer = setInterval(moveSlide, autoSlideInterval);
  }

  // Reset auto-slide timer
  function resetAutoSlide() {
    clearInterval(autoSlideTimer);
    startAutoSlide();
  }

  // Initial status update
  updateArrowStatus();

  // Start auto-sliding
  startAutoSlide();

  // Pause auto-sliding when hovering over carousel
  carouselContainer.addEventListener("mouseenter", function () {
    clearInterval(autoSlideTimer);
  });

  // Resume auto-sliding when mouse leaves
  carouselContainer.addEventListener("mouseleave", function () {
    startAutoSlide();
  });
});
