/**
 * UI Component Logic (Hero, Header)
 */
import { escapeHtml } from "./utils.js";

const hero = document.getElementById("hero");

// ===== HERO SECTION UPDATE =====
export function updateHero(page) {
  if (page === "home") {
    const userText = window.CURRENT_USER
      ? `Bine ai revenit, ${escapeHtml(window.CURRENT_USER)}!`
      : "Bine ai venit la Mazi Coffee";
    hero.innerHTML = `
            <div class="content-wrapper">
                <div class="bgimg">
                    
                </div>
            </div>
        `;
  } else {
    hero.innerHTML = "";
  }
}

// ===== NAV LINK ACTIVE STATE =====
export function setActiveLink(page) {
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.classList.toggle("active", link.dataset.page === page);
  });
}

/**
 * Updates the header UI (Admin link, Profile Popup buttons) based on current session state.
 * Dynamically creates popup buttons if they are missing from the DOM.
 */
export function updateHeaderUI() {
  const roles = window.APP_CONFIG?.currentUserRoles || [];
  const isAdmin = roles.includes("admin");
  const isLoggedIn = !!window.CURRENT_USER;
  const userData = window.APP_CONFIG?.currentUserData || {};

  // 1. Toggle Admin Link
  const adminLinkLi = document.getElementById("admin-link-li");
  if (adminLinkLi) {
    adminLinkLi.style.display = isAdmin ? "" : "none";
  }

  // 2. Toggle Popup Content / Profile Logic
  // unified: profile-buttons is always visible, but we inject extra info if logged in

  // Update Profile Button Text/Icon dynamically
  const profileBtn = document.getElementById("profile-btn");
  if (profileBtn) {
    if (isLoggedIn) {
      const avatarUrl = userData.profile_picture || "assets/public/default.png";
      const displayUser = userData.username || window.CURRENT_USER || "User";
      profileBtn.innerHTML = `
        ${escapeHtml(displayUser)}
        <img src="${escapeHtml(
        avatarUrl
      )}" alt="Profile" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;">
      `;
    } else {
      profileBtn.innerHTML = `
        Cont
        <i class="fa-regular fa-circle-user"></i>
      `;
    }
  }

  // Profile Info Container (inside profile popup)
  let profileInfo = document.getElementById("profile-info");
  const popupContent = document.querySelector(".profile-popup-content");
  const userActions = document.getElementById("popup-user-actions");
  const guestActions = document.getElementById("popup-guest-actions");

  if (isLoggedIn) {
    // Show User Actions, Hide Guest Actions
    if (userActions) userActions.style.display = "flex";
    if (guestActions) guestActions.style.display = "none";

    if (popupContent) {
      // Create Profile Info if missing
      if (!profileInfo) {
        profileInfo = document.createElement("div");
        profileInfo.id = "profile-info";
        profileInfo.style.marginBottom = "15px";
        profileInfo.style.textAlign = "center";
        popupContent.prepend(profileInfo); // Add at top
      }
      // Update Profile Info Content
      const avatarUrl = userData.profile_picture || "assets/public/default.png";
      const points = userData.loyalty_points || 0;
      profileInfo.innerHTML = `
        <div class="avatar-container">
            <div class="avatar-bg-rect"></div>
            <img id="profile-pic-trigger" src="${escapeHtml(avatarUrl)}" alt="Profile" style="cursor:pointer;">
        </div>

        <div style="font-weight:bold;margin-bottom:5px;">${escapeHtml(userData.username)}</div>

        <div style="font-size:1em;color:#fff">
            Your Points: 
            <br>
            <span style="display:inline-block; margin-top:5px; color:#522F25; font-weight:bold; background:#ebdbc0; border-radius:99px; padding:5px;">
                <i class="fa-solid fa-coins"></i> ${points} points
            </span>
            <br/>
        </div>
        <hr style="margin: 10px 0; border: 0; border-top: 0px solid #eee;">
        `;



    }
  } else {
    // Logged OUT: Show Guest Actions, Hide User Actions
    if (userActions) userActions.style.display = "none";
    if (guestActions) guestActions.style.display = "block";

    // cleanup profile info if it exists
    if (profileInfo) profileInfo.remove();
  }
}

/**
 * Initializes the navbar scroll effect.
 */
export function initNavbarScroll() {
  const navbar = document.querySelector(".navbar");
  if (!navbar) return;

  const handleScroll = () => {
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  };

  window.addEventListener("scroll", handleScroll);
  handleScroll(); // Check initial state
}

/**
 * Initializes the material design ripple effect on buttons.
 */
export function initRippleEffect() {
  const selector = ".hero-offer-btn, .auth-btn, button, .product-card .buy-now";

  // We use delegation on document body to handle dynamically added buttons (like in menu or loaded pages)
  document.addEventListener("click", function (e) {
    const target = e.target.closest(selector);

    if (target) {
      // Add utility class if missing (for overflow:hidden)
      if (!target.classList.contains("btn-ripple")) {
        target.classList.add("btn-ripple");
      }

      const circle = document.createElement("span");
      const diameter = Math.max(target.clientWidth, target.clientHeight);
      const radius = diameter / 2;

      const rect = target.getBoundingClientRect();

      circle.style.width = circle.style.height = `${diameter}px`;
      circle.style.left = `${e.clientX - rect.left - radius}px`;
      circle.style.top = `${e.clientY - rect.top - radius}px`;
      circle.classList.add("ripple");

      const ripple = target.getElementsByClassName("ripple")[0];
      if (ripple) {
        ripple.remove();
      }

      target.appendChild(circle);
    }
  });
}

// ===== DELEGATED EVENT LISTENERS =====
// Handle Profile Picture Click (delegated)
document.addEventListener("click", (e) => {
  if (e.target && e.target.id === "profile-pic-trigger") {
    e.preventDefault();
    if (confirm("Vrei să îți schimbi poza de profil?")) {
      import("./profile.js").then((p) => p.closeProfilePopup());

      // Fetch and show modal
      import("./api.js").then(({ safeFetch }) => {
        const routes = window.APP_CONFIG?.routes || {};
        const url = routes["profile_picture_upload"];
        if (url) {
          safeFetch(url)
            .then((res) => res.text())
            .then((html) => {
              import("./utils.js").then(({ showContentModal }) => {
                showContentModal(html);

                // Attach submit listener to the form that was just injected
                // We can use delegation here too optionally, but for now this is fine as it's infrequent
                setTimeout(() => {
                  const form = document.querySelector(
                    ".profile-picture-upload-box"
                  );
                  if (form) {
                    form.addEventListener("submit", function (e) {
                      e.preventDefault();
                      const formData = new FormData(this);
                      const submitBtn = this.querySelector(
                        'button[type="submit"]'
                      );
                      if (submitBtn) submitBtn.disabled = true;

                      safeFetch(this.action, {
                        method: "POST",
                        body: formData,
                      })
                        .then((res) => res.json())
                        .then((data) => {
                          if (data.success) {
                            // Reload to show new picture
                            window.location.reload();
                          } else {
                            alert(data.message || "Upload failed");
                            if (submitBtn) submitBtn.disabled = false;
                          }
                        })
                        .catch((err) => {
                          console.error("Upload error:", err);
                          alert("An error occurred during upload.");
                          if (submitBtn) submitBtn.disabled = false;
                        });
                    });
                  }
                }, 50);
              });
            })
            .catch((err) =>
              console.error("Failed to load upload form", err)
            );
        } else {
          console.error("Route for profile_picture_upload not found");
        }
      });
    }
  }
});
