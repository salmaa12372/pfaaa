document.addEventListener('DOMContentLoaded', () => {

  /* ── Panel toggle ── */
  const btnUp = document.getElementById('btnUp');
  const btnIn = document.getElementById('btnIn');
  const cont  = document.getElementById('cont');

  if (btnUp && btnIn && cont) {
    btnUp.addEventListener('click', () => cont.classList.add('active'));
    btnIn.addEventListener('click', () => cont.classList.remove('active'));
  }

  /* ── Particle canvas ── */
  const canvas = document.getElementById('c');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let W = 0, H = 0;

  function resizeCanvas() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resizeCanvas);
  resizeCanvas();

  /* Bena palette: warm beige/red/green particles */
  const PALETTE = [
    { h: 0,   s: 65, l: 45 },   /* Bena red */
    { h: 140, s: 50, l: 30 },   /* Bena green */
    { h: 30,  s: 60, l: 55 },   /* warm amber */
    { h: 20,  s: 40, l: 70 },   /* soft beige */
  ];

  class Particle {
    constructor(fresh) {
      this.reset(fresh);
    }
    reset(fresh) {
      this.x = Math.random() * W;
      this.y = fresh ? Math.random() * H : H + 10;
      this.r = Math.random() * 2.2 + 0.4;
      this.vx = (Math.random() - 0.5) * 0.45;
      this.vy = -(Math.random() * 0.85 + 0.25);
      this.alpha = Math.random() * 0.45 + 0.12;
      this.decay = Math.random() * 0.0025 + 0.0008;
      const c = PALETTE[Math.floor(Math.random() * PALETTE.length)];
      this.color = `hsl(${c.h}, ${c.s}%, ${c.l}%)`;
    }
    update() {
      this.x += this.vx;
      this.y += this.vy;
      this.alpha -= this.decay;
      if (this.alpha <= 0 || this.y < -10) this.reset(false);
    }
    draw() {
      ctx.save();
      ctx.globalAlpha = this.alpha;
      ctx.fillStyle = this.color;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }
  }

  const particles = Array.from({ length: 90 }, () => new Particle(true));

  function animate() {
    ctx.clearRect(0, 0, W, H);
    for (const p of particles) { p.update(); p.draw(); }
    requestAnimationFrame(animate);
  }
  animate();

});
