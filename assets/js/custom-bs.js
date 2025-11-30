"use strict";

(function () {
  const isWindows = navigator.platform.indexOf("Win") > -1;

  if (isWindows) {
    const mainpanel = document.querySelector(".main-content");
    if (mainpanel) new PerfectScrollbar(mainpanel);

    const sidebar = document.querySelector(".sidenav");
    if (sidebar) new PerfectScrollbar(sidebar);

    const navbarCollapse = document.querySelector(".navbar:not(.navbar-expand-lg) .navbar-collapse");
    if (navbarCollapse) new PerfectScrollbar(navbarCollapse);

    const fixedplugin = document.querySelector(".fixed-plugin");
    if (fixedplugin) new PerfectScrollbar(fixedplugin);
  }

  if (document.getElementById("navbarBlur")) {
    navbarBlurOnScroll("navbarBlur");
  }

  // Tooltip Initialization
  if (typeof window.tooltipTriggerList === "undefined") {
    const tooltipTriggerList = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')];
    window.tooltipTriggerList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Focus/Blur Effects on Input
  const allInputs = document.querySelectorAll(".input-group input.form-control");
  allInputs.forEach((el) => {
    el.addEventListener("focus", () => focused(el));
    el.addEventListener("focusout", () => defocused(el));
  });

  // Sidebar Toggle
  const iconNavbarSidenav = document.getElementById("iconNavbarSidenav");
  const iconSidenav = document.getElementById("iconSidenav");
  const sidenav = document.getElementById("sidenav-main");
  const body = document.body;
  const className = "g-sidenav-pinned";

  function toggleSidenav() {
    if (!sidenav) return;

    if (body.classList.contains(className)) {
      body.classList.remove(className);
      setTimeout(() => sidenav.classList.remove("bg-white"), 100);
      sidenav.classList.remove("bg-transparent");
    } else {
      body.classList.add(className);
      sidenav.classList.add("bg-white");
      sidenav.classList.remove("bg-transparent");
      if (iconSidenav) iconSidenav.classList.remove("d-none");
    }
  }

  if (iconNavbarSidenav) iconNavbarSidenav.addEventListener("click", toggleSidenav);
  if (iconSidenav) iconSidenav.addEventListener("click", toggleSidenav);

  // Optional: Uncomment to hide on outside click
  // document.documentElement.addEventListener("click", function (e) {
  //   if (body.classList.contains(className) && !e.target.closest(".sidenav-toggler-line")) {
  //     body.classList.remove(className);
  //   }
  // });

  function navbarColorOnResize() {
    const referenceButtons = document.querySelector("[data-class]");
    if (!sidenav) return;

    if (window.innerWidth > 1200) {
      if (
        referenceButtons &&
        referenceButtons.classList.contains("active") &&
        referenceButtons.getAttribute("data-class") === "bg-transparent"
      ) {
        sidenav.classList.remove("bg-white");
      } else if (!body.classList.contains("dark-version")) {
        sidenav.classList.add("bg-white");
      }
    } else {
      sidenav.classList.add("bg-white");
      sidenav.classList.remove("bg-transparent");
    }
  }

  function sidenavTypeOnResize() {
    const elements = document.querySelectorAll('[onclick="sidebarType(this)"]');
    elements.forEach((el) => {
      if (window.innerWidth < 1200) el.classList.add("disabled");
      else el.classList.remove("disabled");
    });
  }

  window.addEventListener("resize", navbarColorOnResize);
  window.addEventListener("resize", sidenavTypeOnResize);
  window.addEventListener("load", sidenavTypeOnResize);

  // Utility Functions
  function focused(el) {
    el.parentElement.classList.contains("input-group") && el.parentElement.classList.add("focused");
  }

  function defocused(el) {
    el.parentElement.classList.contains("input-group") && el.parentElement.classList.remove("focused");
  }

  function debounce(func, wait, immediate) {
    let timeout;
    return function () {
      const context = this,
        args = arguments;
      const later = function () {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      const callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  }

  function navbarBlurOnScroll(id) {
    const navbar = document.getElementById(id);
    const navbarScrollActive = navbar?.getAttribute("data-scroll") === "true";
    const scrollDistance = 5;
    const classes = ["bg-white", "left-auto", "position-sticky"];
    const toggleClasses = ["shadow-none"];

    function blurNavbar() {
      navbar.classList.add(...classes);
      navbar.classList.remove(...toggleClasses);
      toggleNavLinksColor("blur");
    }

    function transparentNavbar() {
      navbar.classList.remove(...classes);
      navbar.classList.add(...toggleClasses);
      toggleNavLinksColor("transparent");
    }

    if (navbarScrollActive) {
      window.onscroll = debounce(() => {
        window.scrollY > scrollDistance ? blurNavbar() : transparentNavbar();
      }, 10);
    } else {
      window.onscroll = debounce(() => transparentNavbar(), 10);
    }

    if (isWindows) {
      const content = document.querySelector(".main-content");
      if (content) {
        content.addEventListener(
          "ps-scroll-y",
          debounce(() => {
            content.scrollTop > scrollDistance ? blurNavbar() : transparentNavbar();
          }, 10)
        );
      }
    }
  }

  function toggleNavLinksColor(type) {
    const navLinks = document.querySelectorAll(
      ".navbar-main .nav-link, .navbar-main .breadcrumb-item, .navbar-main .breadcrumb-item a, .navbar-main h6"
    );
    const navLinksToggler = document.querySelectorAll(".navbar-main .sidenav-toggler-line");

    if (type === "blur") {
      navLinks.forEach((el) => el.classList.remove("text-white"));
      navLinksToggler.forEach((el) => {
        el.classList.add("bg-dark");
        el.classList.remove("bg-white");
      });
    } else {
      navLinks.forEach((el) => el.classList.add("text-white"));
      navLinksToggler.forEach((el) => {
        el.classList.remove("bg-dark");
        el.classList.add("bg-white");
      });
    }
  }
})();
