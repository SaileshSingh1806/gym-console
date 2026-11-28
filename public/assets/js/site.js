(() => {
  const root = document.querySelector('.marketing-site');
  if (!root) return;
  const steps = [...root.querySelectorAll('[data-story-step]')];
  const screens = [...root.querySelectorAll('[data-story-screen]')];
  const nav = [...root.querySelectorAll('.gd-feature-nav a')];
  let pending = false;
  function update() {
    pending = false;
    if (steps.length) {
      const target = steps.reduce((best, el) => Math.abs(el.getBoundingClientRect().top + el.offsetHeight/2 - innerHeight/2) < Math.abs(best.getBoundingClientRect().top + best.offsetHeight/2 - innerHeight/2) ? el : best);
      screens.forEach(el => el.classList.toggle('active', el.dataset.storyScreen === target.dataset.storyStep));
    }
    let current = nav[0];
    for (const link of nav) {
      const section = document.getElementById(link.hash.slice(1));
      if (section && section.getBoundingClientRect().top <= 180) current = link;
    }
    nav.forEach(link => {
      link.classList.toggle('active', link === current);
      if (link === current) link.setAttribute('aria-current', 'location');
      else link.removeAttribute('aria-current');
    });
  }
  function schedule() { if (!pending) { pending = true; requestAnimationFrame(update); } }
  addEventListener('scroll', schedule, {passive:true});
  addEventListener('resize', schedule, {passive:true});
  root.querySelectorAll('details').forEach(el => {
    const summary = el.querySelector('summary');
    if (!summary) return;
    const sync = () => summary.setAttribute('aria-expanded', String(el.open));
    sync(); el.addEventListener('toggle', sync);
  });
  update();
})();