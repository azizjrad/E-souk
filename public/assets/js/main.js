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

  document.addEventListener("DOMContentLoaded", function () {
    // Get carousel elements
    const carousel = document.querySelector(".product-carousel");
    const prevArrow = document.querySelector(".prev-arrow");
    const nextArrow = document.querySelector(".next-arrow");

    // Exit if elements don't exist
    if (!carousel || !prevArrow || !nextArrow) {
      console.error("Carousel elements not found!");
      return;
    }

    // Set scroll amount
    const scrollAmount = 300; // Adjust based on card width
    let position = 0;

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

    // Initialize
    updateCarousel();

    // Previous button click handler
    prevArrow.addEventListener("click", function () {
      position = Math.max(0, position - scrollAmount);
      updateCarousel();
    });

    // Next button click handler
    nextArrow.addEventListener("click", function () {
      position = Math.min(getMaxScroll(), position + scrollAmount);
      updateCarousel();
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
  });
});
