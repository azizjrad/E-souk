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

  // Initialize all handlers
  handleDropdowns();
  handleBackToTop();

  // Close dropdowns when clicking outside
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".dropdown-submenu") && window.innerWidth <= 992) {
      document
        .querySelectorAll(".dropdown-submenu .dropdown-menu")
        .forEach((menu) => {
          menu.classList.remove("show");
        });
    }
  });
});
