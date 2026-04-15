const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/* ── Navbar scroll state ── */
const navbar = $("#navbar");

function updateNavbarScrolled() {
  if (!navbar) return;
  navbar.classList.toggle("scrolled", window.scrollY > 60);
}

window.addEventListener("scroll", updateNavbarScrolled, { passive: true });
updateNavbarScrolled();

/* ── Mobile menu ── */
const menuToggle = $("#menuToggle");
const navLinks   = $("#navLinks");

function closeMenu() {
  if (!navLinks || !menuToggle) return;
  navLinks.classList.remove("open");
  menuToggle.setAttribute("aria-expanded", "false");
}

function toggleMenu() {
  if (!navLinks || !menuToggle) return;
  const isOpen = navLinks.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", String(isOpen));
}

menuToggle?.addEventListener("click", (e) => {
  e.stopPropagation();
  toggleMenu();
});

$$("a", navLinks).forEach((a) => a.addEventListener("click", closeMenu));

document.addEventListener("click", (e) => {
  if (!navLinks || !menuToggle) return;
  if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) closeMenu();
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeMenu();
});

/* ── Dark / light theme toggle ── */
const toggle    = document.getElementById("holo-toggle");
const THEME_KEY = "bena-theme";

function setTheme(theme) {
  document.body.classList.toggle("dark", theme === "dark");
  if (toggle) toggle.checked = theme === "dark";
}

const savedTheme = localStorage.getItem(THEME_KEY);
if (savedTheme) {
  setTheme(savedTheme);
} else {
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  setTheme(prefersDark ? "dark" : "light");
}

toggle?.addEventListener("change", () => {
  const theme = toggle.checked ? "dark" : "light";
  setTheme(theme);
  localStorage.setItem(THEME_KEY, theme);
});

/* ── Intersection Observer — scroll animations ── */
const io = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        io.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.1, rootMargin: "0px 0px -30px 0px" }
);

$$(".fade-in, .slide-in-left").forEach((el) => io.observe(el));

/* ── Add-to-cart micro-interaction ── */
$$(".pc-add").forEach((btn) => {
  btn.addEventListener("click", function () {
    const orig = this.innerHTML;
    this.innerHTML = "✓";
    this.style.background = "var(--green)";
    this.style.color      = "white";
    this.style.transform  = "scale(1.12)";

    setTimeout(() => {
      this.innerHTML       = orig;
      this.style.background = "";
      this.style.color      = "";
      this.style.transform  = "";
    }, 1400);
  });
});

/* ── Contact form ── */
$("#subscribeForm")?.addEventListener("submit", function (e) {
  e.preventDefault();
  const btn = $("#submitBtn");
  if (!btn) return;

  const orig = btn.textContent;
  btn.textContent   = "✓ Welcome to the Circle!";
  btn.style.background = "var(--gold)";
  btn.style.color      = "white";

  setTimeout(() => {
    btn.textContent      = orig;
    btn.style.background = "";
    btn.style.color      = "";
  }, 2400);

  this.reset();
});

/* ── Gallery Carousel ── */
const gallery = [
  {
    title: "Stone Oven Baking",
    desc:  "Traditional Tunisian tabouna oven, signature crunch and golden hue.",
    img:   "pics/gallery-1.jpg",
    tags:  ["Stone-baked", "Heritage"],
  },
  {
    title: "Harvesting Honey",
    desc:  "Wild Jebel honey from northern Tunisia — pure, unfiltered, alive.",
    img:   "pics/gallery-2.jpg",
    tags:  ["Wild Honey", "Organic"],
  },
  {
    title: "Sfax Sesame Fields",
    desc:  "Toasted sesame from Sfax, rich in flavour and tradition.",
    img:   "pics/gallery-3.jpg",
    tags:  ["Local", "Premium"],
  },
  {
    title: "Handcrafting Process",
    desc:  "Each bite shaped by hand — small batches, unhurried care.",
    img:   "pics/gallery-4.jpg",
    tags:  ["Artisan", "Small batch"],
  },
];

let activeIndex = 0;
let autoInterval = null;

const stage    = $("#carouselStage");
const dotsWrap = $("#carouselDots");
const scene    = $("#carouselScene");

function buildCarousel() {
  if (!stage || !dotsWrap) return;

  stage.innerHTML    = "";
  dotsWrap.innerHTML = "";

  gallery.forEach((item, idx) => {
    const card = document.createElement("div");
    card.className = "gc-card";
    card.innerHTML = `
      <img src="${item.img}" alt="${item.title}" loading="lazy">
      <div class="gc-body">
        <div class="gc-title">${item.title}</div>
        <p class="gc-desc">${item.desc}</p>
        <div class="gc-tags">${item.tags.map(t => `<span class="gc-tag">${t}</span>`).join("")}</div>
      </div>
    `;
    card.addEventListener("click", () => goTo(idx));
    stage.appendChild(card);

    const dot = document.createElement("div");
    dot.className = "c-dot" + (idx === 0 ? " active" : "");
    dot.addEventListener("click", () => goTo(idx));
    dotsWrap.appendChild(dot);
  });

  positionSlides(false);
}

function positionSlides(animate) {
  const cards = $$(".gc-card");
  const dots  = $$(".c-dot");
  const total = gallery.length;

  cards.forEach((card, i) => {
    let offset = (i - activeIndex + total) % total;
    if (offset > total / 2) offset -= total;

    let transform, opacity, zIndex, pointer;

    if (offset === 0) {
      transform = "translateX(0) rotateY(0deg) scale(1)";
      opacity   = 1;
      zIndex    = 10;
      pointer   = "auto";
    } else if (offset === 1) {
      transform = "translateX(280px) rotateY(-25deg) scale(0.82)";
      opacity   = 0.62;
      zIndex    = 6;
      pointer   = "auto";
    } else if (offset === -1) {
      transform = "translateX(-280px) rotateY(25deg) scale(0.82)";
      opacity   = 0.62;
      zIndex    = 6;
      pointer   = "auto";
    } else {
      transform = `translateX(${offset * 520}px) scale(0.55)`;
      opacity   = 0;
      zIndex    = 1;
      pointer   = "none";
    }

    card.style.transition   = animate
      ? "transform .7s cubic-bezier(0.23,1,0.32,1), opacity .45s ease"
      : "none";
    card.style.transform    = transform;
    card.style.opacity      = opacity;
    card.style.zIndex       = zIndex;
    card.style.pointerEvents = pointer;
  });

  dots.forEach((d, i) => d.classList.toggle("active", i === activeIndex));
}

function goTo(idx) {
  activeIndex = (idx + gallery.length) % gallery.length;
  positionSlides(true);
  resetAuto();
}

function next() { goTo(activeIndex + 1); }
function prev() { goTo(activeIndex - 1); }

function startAuto() {
  stopAuto();
  autoInterval = setInterval(next, 5000);
}

function stopAuto() {
  if (autoInterval) clearInterval(autoInterval);
  autoInterval = null;
}

function resetAuto() { startAuto(); }

$("#carouselPrev")?.addEventListener("click", () => { prev(); resetAuto(); });
$("#carouselNext")?.addEventListener("click", () => { next(); resetAuto(); });

/* Touch swipe for carousel */
let touchStartX = null;

scene?.addEventListener("touchstart", (e) => {
  touchStartX = e.touches[0].clientX;
}, { passive: true });

scene?.addEventListener("touchend", (e) => {
  if (touchStartX == null) return;
  const diff = e.changedTouches[0].clientX - touchStartX;
  if (Math.abs(diff) > 40) diff < 0 ? next() : prev();
  resetAuto();
  touchStartX = null;
});

scene?.addEventListener("mouseenter", stopAuto);
scene?.addEventListener("mouseleave", startAuto);

buildCarousel();
startAuto();