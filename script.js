const navLinks = document.getElementById("navLinks");
const menuToggle = document.querySelector(".menu-toggle");
const serviceModal = document.getElementById("serviceModal");
const modalTitle = document.getElementById("modalTitle");
const modalText = document.getElementById("modalText");
const complaintForm = document.getElementById("complaintForm");
const complaintMessage = document.getElementById("complaintMessage");
const fileInput = document.getElementById("photoInput");
const fileLabel = document.getElementById("fileLabel");
const locationButton = document.getElementById("locationButton");
const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const arabicDigits = new Intl.NumberFormat("ar-EG", { maximumFractionDigits: 0 });

function setMenu(open) {
  navLinks.classList.toggle("is-open", open);
  document.body.classList.toggle("menu-open", open);
  menuToggle.setAttribute("aria-expanded", String(open));
  menuToggle.setAttribute("aria-label", open ? "إغلاق القائمة" : "فتح القائمة");
}

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", () => {
    setMenu(!navLinks.classList.contains("is-open"));
  });

  navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => setMenu(false));
  });
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
  link.addEventListener("click", (event) => {
    const target = document.querySelector(link.getAttribute("href"));
    if (!target) return;
    event.preventDefault();
    target.scrollIntoView({ behavior: prefersReducedMotion ? "auto" : "smooth", block: "start" });
  });
});

document.querySelectorAll(".service-action").forEach((button) => {
  button.addEventListener("click", () => {
    const serviceName = button.dataset.service;
    modalTitle.textContent = serviceName;
    modalText.textContent = `سيتم فتح نموذج خدمة ${serviceName} بعد ربط البوابة بنظام البلدية.`;
    serviceModal.classList.add("is-open");
    serviceModal.setAttribute("aria-hidden", "false");
  });
});

document.querySelectorAll("[data-close-modal]").forEach((button) => {
  button.addEventListener("click", () => {
    serviceModal.classList.remove("is-open");
    serviceModal.setAttribute("aria-hidden", "true");
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && serviceModal.classList.contains("is-open")) {
    serviceModal.classList.remove("is-open");
    serviceModal.setAttribute("aria-hidden", "true");
  }
});

if (fileInput && fileLabel) {
  fileInput.addEventListener("change", () => {
    fileLabel.textContent = fileInput.files.length ? "تم اختيار صورة" : "اختيار صورة";
  });
}

if (locationButton) {
  locationButton.addEventListener("click", () => {
    locationButton.textContent = "تم تحديد الموقع التقريبي";
  });
}

if (complaintForm) {
  complaintForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const requiredFields = complaintForm.querySelectorAll("[required]");
    let valid = true;

    requiredFields.forEach((field) => {
      const hasValue = field.value.trim().length > 0;
      field.classList.toggle("invalid", !hasValue);
      if (!hasValue) valid = false;
    });

    if (!valid) {
      complaintMessage.textContent = "يرجى تعبئة الحقول المطلوبة قبل الإرسال.";
      return;
    }

    complaintMessage.textContent = "تم استلام الشكوى بنجاح، وسيتم إرسال رقم المتابعة عبر الهاتف.";
    complaintForm.reset();
    fileLabel.textContent = "اختيار صورة";
    locationButton.textContent = "تحديد الموقع التقريبي";
  });
}

document.querySelectorAll(".contact-form").forEach((form) => {
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const button = form.querySelector("button[type='submit']");
    button.textContent = "تم إرسال الرسالة";
    form.reset();
    window.setTimeout(() => {
      button.textContent = "إرسال الرسالة";
    }, 2200);
  });
});

function animateCount(element) {
  const target = Number(element.dataset.target || 0);
  const duration = 1100;
  const start = performance.now();

  function tick(now) {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    element.textContent = arabicDigits.format(Math.round(target * eased));

    if (progress < 1) {
      requestAnimationFrame(tick);
    }
  }

  requestAnimationFrame(tick);
}

const statsGrid = document.getElementById("statsGrid");
if (statsGrid) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.querySelectorAll(".count").forEach(animateCount);
      observer.disconnect();
    });
  }, { threshold: 0.3 });

  observer.observe(statsGrid);
}

function createMapMarker(type = "default") {
  if (!window.L) return null;

  return L.divIcon({
    className: "",
    html: `<span class="municipal-map-marker ${type}"></span>`,
    iconSize: [26, 26],
    iconAnchor: [13, 26],
    popupAnchor: [0, -24]
  });
}

function initializeQabatiyaMap() {
  const mapElement = document.getElementById("qabatiyaMap");
  if (!mapElement || !window.L || mapElement.dataset.loaded === "true") return;

  mapElement.dataset.loaded = "true";
  const qabatiyaCoords = [32.4106, 35.2809];

  const map = L.map("qabatiyaMap", {
    scrollWheelZoom: false,
    attributionControl: false
  }).setView(qabatiyaCoords, 15);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19
  }).addTo(map);

  L.control.attribution({ prefix: false })
    .addAttribution('© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">مساهمو خريطة الشارع المفتوحة</a>')
    .addTo(map);

  const locations = [
    {
      title: "بلدية قباطية",
      coords: [32.4106, 35.2809],
      description: "الموقع الرئيسي لبلدية قباطية",
      type: "default"
    },
    {
      title: "البلدة القديمة",
      coords: [32.4120, 35.2788],
      description: "منطقة تراثية داخل المدينة",
      type: "default"
    },
    {
      title: "منطقة خدمات",
      coords: [32.4094, 35.2832],
      description: "منطقة خدمات بلدية للمواطنين",
      type: "services"
    },
    {
      title: "مشروع تعبيد",
      coords: [32.4078, 35.2814],
      description: "مشروع تعبيد وتحسين شارع",
      type: "projects"
    },
    {
      title: "موقع شكوى",
      coords: [32.4131, 35.2824],
      description: "بلاغ مواطن قيد المتابعة",
      type: "complaints"
    }
  ];

  locations.forEach((location) => {
    L.marker(location.coords, { icon: createMapMarker(location.type) })
      .addTo(map)
      .bindPopup(`
        <strong>${location.title}</strong>
        ${location.description}
      `);
  });

  const refreshMap = () => window.setTimeout(() => map.invalidateSize(), 250);
  refreshMap();
  window.addEventListener("resize", refreshMap);
  const mapObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) refreshMap();
    });
  }, { threshold: 0.25 });

  mapObserver.observe(mapElement);
}
initializeQabatiyaMap();
if (window.location.hash === "#map") {
  window.setTimeout(() => {
    document.getElementById("map")?.scrollIntoView({ behavior: "auto", block: "start" });
  }, 700);
}

if (window.gsap && window.ScrollTrigger && !prefersReducedMotion) {
  gsap.registerPlugin(window.ScrollTrigger);

  gsap.from(".site-header", {
    y: -40,
    opacity: 0,
    duration: 0.7,
    ease: "power3.out"
  });

  gsap.timeline({ defaults: { duration: 0.85, ease: "power3.out" } })
    .from(".hero-kicker", { y: 28, opacity: 0 })
    .from(".hero h1", { y: 34, opacity: 0 }, "-=0.55")
    .from(".hero-subtitle", { y: 32, opacity: 0 }, "-=0.55")
    .from(".hero-description", { y: 28, opacity: 0 }, "-=0.5")
    .from(".hero-actions .btn", { y: 24, opacity: 0, stagger: 0.12 }, "-=0.45");

  gsap.to(".float-shape", {
    y: 12,
    x: 6,
    rotation: 1.5,
    duration: 4.6,
    repeat: -1,
    yoyo: true,
    ease: "sine.inOut",
    stagger: 0.45
  });

  gsap.utils.toArray(".reveal").forEach((element) => {
    gsap.from(element, {
      scrollTrigger: {
        trigger: element,
        start: "top 86%"
      },
      y: 34,
      opacity: 0,
      duration: 0.78,
      ease: "power3.out"
    });
  });

  gsap.from(".stat-card", {
    scrollTrigger: {
      trigger: ".stats-grid",
      start: "top 84%"
    },
    scale: 0.94,
    opacity: 0,
    duration: 0.65,
    stagger: 0.08,
    ease: "power3.out"
  });
}
