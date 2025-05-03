/**
 * E-Souk Tounsi - Wishlist Functionality
 * Handles wishlist operations only
 */

// Make sure ROOT_URL is defined
if (typeof ROOT_URL === "undefined") {
  console.error(
    "ROOT_URL is not defined. Please define it before including wishlist.js"
  );
}

/**
 * Initialize wishlist functionality on page load
 */
document.addEventListener("DOMContentLoaded", function () {
  initWishlistButtons();
  initWishlistClearButton();
  wishlist.ensureToastContainer();
  syncWishlistButtonStates();

  // Initialize wishlist data in sessionStorage if not exists
  if (
    !sessionStorage.getItem("wishlistedProducts") &&
    typeof wishlisted_product_ids !== "undefined"
  ) {
    sessionStorage.setItem(
      "wishlistedProducts",
      JSON.stringify(wishlisted_product_ids)
    );
  }
});

// Namespace for wishlist functionality
const wishlist = {
  /**
   * Show toast notification (wishlist-specific implementation)
   */
  showToast: function (message, type = "primary") {
    const container = this.ensureToastContainer();

    const toastId = "wishlist-toast-" + Date.now();
    const toast = document.createElement("div");
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

    container.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();

    toast.addEventListener("hidden.bs.toast", function () {
      this.remove();
    });
  },

  /**
   * Ensure toast container exists
   */
  ensureToastContainer: function () {
    let container = document.getElementById("toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "toast-container";
      container.className = "toast-container position-fixed bottom-0 end-0 p-3";
      container.style.zIndex = "1050";
      document.body.appendChild(container);
    }
    return container;
  },

  /**
   * Get HTML for empty wishlist state
   */
  getEmptyWishlistHTML: function () {
    return `
            <div class="col-12 empty-wishlist">
                <div class="text-center py-5">
                    <i class="far fa-heart wishlist-empty-icon"></i>
                    <h3>Votre liste de favoris est vide</h3>
                    <p>Ajoutez des produits à votre liste pour les retrouver facilement plus tard</p>
                    <a href="${ROOT_URL}public/pages/product.php" class="btn btn-medium mt-3">Découvrir les produits</a>
                </div>
            </div>
        `;
  },
};

/**
 * Initialize all wishlist toggle buttons
 */
function initWishlistButtons() {
  console.log("Initializing wishlist buttons");
  document
    .querySelectorAll(".wishlist-btn, .wishlist-icon, [data-product-id]")
    .forEach((button) => {
      if (!button.hasAttribute("data-wishlist-initialized")) {
        button.setAttribute("data-wishlist-initialized", "true");

        // Remove existing onclick attributes for consistency
        if (button.hasAttribute("onclick")) {
          const productId = button.getAttribute("data-product-id");
          const originalHandler = button.getAttribute("onclick");
          button.removeAttribute("onclick");

          button.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleWishlist(productId, this);
          });
        } else if (
          button.classList.contains("wishlist-btn") ||
          button.classList.contains("wishlist-icon")
        ) {
          const productId = button.getAttribute("data-product-id");
          if (productId) {
            button.addEventListener("click", function (e) {
              e.preventDefault();
              e.stopPropagation();
              toggleWishlist(productId, this);
            });
          }
        }
      }
    });
}

/**
 * Initialize clear wishlist button if it exists
 */
function initWishlistClearButton() {
  const clearButton = document.getElementById("clear-wishlist");
  if (clearButton && !clearButton.hasAttribute("data-wishlist-initialized")) {
    clearButton.setAttribute("data-wishlist-initialized", "true");

    clearButton.addEventListener("click", function () {
      if (confirm("Êtes-vous sûr de vouloir vider votre liste de favoris?")) {
        clearWishlist();
      }
    });
  }
}

/**
 * Display a toast notification if available
 * @param {string} message - Message to display
 * @param {string} type - Type of toast (success, info, error)
 */
function displayWishlistToast(message, type) {
  if (
    typeof wishlist !== "undefined" &&
    typeof wishlist.showToast === "function"
  ) {
    wishlist.showToast(message, type);
  }
}

/**
 * Update wishlist UI based on action
 * @param {HTMLElement} element - Button element
 * @param {string} action - Action performed (added/removed)
 * @param {string} message - Message from server
 */
function updateWishlistUI(element, action, message) {
  const icon = element.querySelector("i") || element;

  if (action === "added") {
    if (icon.classList.contains("far")) {
      icon.classList.remove("far");
      icon.classList.add("fas");
    }
    icon.classList.add("active");
    displayWishlistToast(message || "Produit ajouté aux favoris", "success");
  } else {
    if (icon.classList.contains("fas")) {
      icon.classList.remove("fas");
      icon.classList.add("far");
    }
    icon.classList.remove("active");
    displayWishlistToast(message || "Produit retiré des favoris", "info");
  }
}

/**
 * Update the wishlist counter in the UI
 * @param {number} count - New wishlist count
 */
function updateWishlistCounter(count) {
  // Find all wishlist counters in the navbar
  const wishlistCounters = document.querySelectorAll(".wishlist-count");

  if (wishlistCounters.length > 0) {
    // Update each counter with the new count
    wishlistCounters.forEach((counter) => {
      counter.textContent = count;

      // Make sure the badge is visible when count > 0
      if (parseInt(count) > 0) {
        counter.style.display = "inline-block";
        counter.classList.add("has-items");
      } else {
        counter.classList.remove("has-items");
      }
    });

    console.log("Wishlist count updated to:", count);
  } else {
    console.warn("Wishlist counter elements not found in the navbar");
  }
}

/**
 * Handle error in wishlist operation
 * @param {string} message - Error message
 */
function handleWishlistError(message) {
  console.error(message || "Une erreur est survenue");
  displayWishlistToast(message || "Une erreur est survenue", "error");
}

/**
 * Toggle product in wishlist
 * @param {number} productId - The product ID to toggle
 * @param {HTMLElement} element - The wishlist icon element
 */
function toggleWishlist(productId, element) {
  if (!productId) {
    console.error("Product ID is required");
    return;
  }

  console.log("Toggling wishlist for product:", productId);
  const formData = new FormData();
  formData.append("product_id", productId);

  fetch(ROOT_URL + "public/pages/toggle_wishlist.php", {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response error: " + response.status);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        updateWishlistUI(element, data.action, data.message);
        updateWishlistCounter(data.wishlist_count);

        // Update sessionStorage with current wishlist state
        let wishlistedProducts = [];
        if (sessionStorage.getItem("wishlistedProducts")) {
          wishlistedProducts = JSON.parse(
            sessionStorage.getItem("wishlistedProducts")
          );
        }

        if (data.action === "added") {
          if (!wishlistedProducts.includes(parseInt(productId))) {
            wishlistedProducts.push(parseInt(productId));
          }
        } else {
          wishlistedProducts = wishlistedProducts.filter(
            (id) => id !== parseInt(productId)
          );
        }

        sessionStorage.setItem(
          "wishlistedProducts",
          JSON.stringify(wishlistedProducts)
        );
      } else {
        handleWishlistError(data.message);
      }
    })
    .catch((error) => {
      console.error("Error toggling wishlist:", error);
      handleWishlistError("Erreur de connexion au serveur");
    });
}

/**
 * Validate wishlist elements before clearing
 * @returns {Object|null} Validation result or null if invalid
 */
function validateWishlistForClearing() {
  const wishlistContainer = document.querySelector(".wishlist-container");
  if (!wishlistContainer) {
    console.error("Wishlist container not found");
    wishlist.showToast("Erreur lors de la suppression", "danger");
    return null;
  }

  const productItems = Array.from(document.querySelectorAll(".product-item"));
  if (productItems.length === 0) {
    wishlist.showToast("Aucun produit à supprimer", "info");
    return null;
  }

  return { wishlistContainer, productItems };
}

/**
 * Handle successful wishlist clearing
 * @param {HTMLElement} container - The wishlist container element
 */
function handleWishlistClearSuccess(container) {
  setTimeout(() => {
    container.innerHTML = wishlist.getEmptyWishlistHTML();

    const clearBtn = document.getElementById("clear-wishlist");
    if (clearBtn) {
      clearBtn.style.display = "none";
    }

    updateWishlistCountBadge(0);
    wishlist.showToast("Votre liste de favoris a été vidée", "success");
  }, 300);
}

/**
 * Handle wishlist clearing error
 * @param {Array} items - Product items to restore
 * @param {string} message - Error message
 */
function handleWishlistClearError(items, message) {
  items.forEach((item) => item.classList.remove("removing"));
  wishlist.showToast(message, "danger");
}

/**
 * Clear entire wishlist
 */
function clearWishlist() {
  const validation = validateWishlistForClearing();
  if (!validation) return;

  const { wishlistContainer, productItems } = validation;

  // Add visual feedback
  productItems.forEach((item) => item.classList.add("removing"));

  fetch(ROOT_URL + "public/pages/toggle_wishlist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: "clear_wishlist=1",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok: " + response.status);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        handleWishlistClearSuccess(wishlistContainer);
      } else {
        handleWishlistClearError(
          productItems,
          data.message || "Erreur lors de la suppression"
        );
      }
    })
    .catch((error) => {
      console.error("Clear wishlist error:", error);
      handleWishlistClearError(
        productItems,
        "Une erreur s'est produite: " + error.message
      );
    });
}

/**
 * Update wishlist count badge in navbar
 */
function updateWishlistCountBadge(count) {
  document.querySelectorAll(".wishlist-count").forEach((badge) => {
    badge.textContent = count.toString();
    badge.style.display = count > 0 ? "inline-block" : "none";
  });
}

/**
 * Synchronize wishlist button states with session storage
 * This ensures the UI reflects the wishlist state during the current browser session
 */
function syncWishlistButtonStates() {
  // Check if we have wishlist data in sessionStorage
  if (sessionStorage.getItem("wishlistedProducts")) {
    const wishlistedProducts = JSON.parse(
      sessionStorage.getItem("wishlistedProducts")
    );

    // Update all wishlist buttons based on sessionStorage data
    document
      .querySelectorAll(".wishlist-btn, .wishlist-icon")
      .forEach((button) => {
        const productId = button.getAttribute("data-product-id");
        if (productId && wishlistedProducts.includes(parseInt(productId))) {
          const icon = button.querySelector("i") || button;
          if (icon.classList.contains("far")) {
            icon.classList.remove("far");
            icon.classList.add("fas");
          }
        }
      });
  }
}
