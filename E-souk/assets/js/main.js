// Enable nested dropdowns on hover (desktop)
document.addEventListener("DOMContentLoaded", function () {
  const dropdowns = document.querySelectorAll(".dropdown-submenu");
  dropdowns.forEach(function (item) {
    item.addEventListener("mouseenter", function () {
      this.querySelector(".dropdown-menu").classList.add("show");
    });
    item.addEventListener("mouseleave", function () {
      this.querySelector(".dropdown-menu").classList.remove("show");
    });
  });
});

// Button to the top
document.addEventListener("DOMContentLoaded", function () {
  const backToTopBtn = document.getElementById("backToTop");

  // Only run if not mobile device
  if (window.innerWidth > 768) {
    window.addEventListener("scroll", function () {
      if (window.pageYOffset > 300) {
        backToTopBtn.style.display = "flex";
      } else {
        backToTopBtn.style.display = "none";
      }
    });

    backToTopBtn.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }
});
