document.addEventListener("DOMContentLoaded", function () {
  // Nested dropdowns handling
  const handleDropdowns = () => {
    const dropdowns = document.querySelectorAll(".dropdown-submenu");

    // Desktop hover handling
    if (window.innerWidth > 992) {
      dropdowns.forEach((item) => {
        item.addEventListener("mouseenter", () => {
          item.querySelector(".dropdown-menu").classList.add("show");
        });
        item.addEventListener("mouseleave", () => {
          item.querySelector(".dropdown-menu").classList.remove("show");
        });
      });
    }
    // Mobile touch handling
    else {
      dropdowns.forEach((item) => {
        const link = item.querySelector(".dropdown-toggle");
        if (!link) return; // Prevent error if .dropdown-toggle is missing
        link.addEventListener("click", (e) => {
          e.preventDefault();
          const menu = item.querySelector(".dropdown-menu");
          menu.classList.toggle("show");

          // Close other submenus
          dropdowns.forEach((otherItem) => {
            if (otherItem !== item) {
              otherItem
                .querySelector(".dropdown-menu")
                .classList.remove("show");
            }
          });
        });
      });
    }
  };

  // Back to top button
  const handleBackToTop = () => {
    const backToTopBtn = document.getElementById("backToTop");

    const toggleVisibility = () => {
      if (window.pageYOffset > 300 && window.innerWidth > 768) {
        backToTopBtn.style.display = "flex";
      } else {
        backToTopBtn.style.display = "none";
      }
    };

    if (window.innerWidth > 768) {
      window.addEventListener("scroll", toggleVisibility);
      backToTopBtn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    }

    // Handle window resize
    window.addEventListener("resize", () => {
      toggleVisibility();
      handleDropdowns(); // Re-initialize dropdown handling on resize
    });
  };

  // Product carousel functionality
  const handleProductCarousel = () => {
    // Get carousel elements
    const carousel = document.querySelector(".product-carousel");
    const prevArrow = document.querySelector(".prev-arrow");
    const nextArrow = document.querySelector(".next-arrow");

    // Exit if elements don't exist
    if (!carousel) {
      console.error("Carousel element not found!");
      return;
    }
    if (!prevArrow) {
      console.error("Previous arrow element not found!");
      return;
    }
    if (!nextArrow) {
      console.error("Next arrow element not found!");
      return;
    }

    // Set scroll amount
    const scrollAmount = 300; // Adjust based on card width
    let position = 0;
    let autoSlideInterval;

    // Get max scroll position
    function getMaxScroll() {
      return carousel.scrollWidth - carousel.parentElement.clientWidth;
    }

    // Update carousel position and button states
    function updateCarousel() {
      carousel.style.transform = `translateX(-${position}px)`;

      // Update button states
      prevArrow.disabled = position <= 0;
      prevArrow.style.opacity = position <= 0 ? "0.5" : "1";

      nextArrow.disabled = position >= getMaxScroll();
      nextArrow.style.opacity = position >= getMaxScroll() ? "0.5" : "1";
    }

    // Move carousel right
    function moveRight() {
      const maxScroll = getMaxScroll();
      if (position >= maxScroll) {
        // If at the end, go back to start
        position = 0;
      } else {
        position = Math.min(maxScroll, position + scrollAmount);
      }
      updateCarousel();
    }

    // Move carousel left
    function moveLeft() {
      if (position <= 0) {
        // If at the start, go to end
        position = getMaxScroll();
      } else {
        position = Math.max(0, position - scrollAmount);
      }
      updateCarousel();
    }

    // Initialize
    updateCarousel();

    // Previous button click handler
    prevArrow.addEventListener("click", function () {
      moveLeft();
      resetAutoSlide(); // Reset the timer when user interacts
    });

    // Next button click handler
    nextArrow.addEventListener("click", function () {
      moveRight();
      resetAutoSlide(); // Reset the timer when user interacts
    });

    // Auto slide functionality
    function startAutoSlide() {
      autoSlideInterval = setInterval(function () {
        moveRight();
      }, 4000); // Slide every 4 seconds
    }

    // Reset auto slide timer
    function resetAutoSlide() {
      clearInterval(autoSlideInterval);
      startAutoSlide();
    }

    // Start auto sliding
    startAutoSlide();

    // Pause auto slide when hovering over carousel
    carousel.addEventListener("mouseenter", function () {
      clearInterval(autoSlideInterval);
    });

    // Resume auto slide when mouse leaves carousel
    carousel.addEventListener("mouseleave", function () {
      startAutoSlide();
    });

    // Touch/swipe support for mobile
    let startX = 0;
    let isSwiping = false;

    carousel.addEventListener("touchstart", function (e) {
      if (e.touches.length === 1) {
        startX = e.touches[0].clientX;
        isSwiping = true;
      }
    });

    carousel.addEventListener(
      "touchmove",
      function (e) {
        // Prevent horizontal scroll on swipe
        if (isSwiping) e.preventDefault();
      },
      { passive: false }
    );

    carousel.addEventListener("touchend", function (e) {
      if (!isSwiping) return;
      const endX = e.changedTouches[0].clientX;
      const diffX = endX - startX;
      const threshold = 50; // Minimum px to be considered a swipe
      if (diffX > threshold) {
        moveLeft();
        resetAutoSlide();
      } else if (diffX < -threshold) {
        moveRight();
        resetAutoSlide();
      }
      isSwiping = false;
    });

    // Update on window resize
    window.addEventListener("resize", function () {
      // Ensure we don't scroll beyond the end after resize
      const maxScroll = getMaxScroll();
      if (position > maxScroll) {
        position = maxScroll;
      }
      updateCarousel();
    });
  };

  // Initialize all components
  handleDropdowns();
  handleBackToTop();
  handleProductCarousel();
});
