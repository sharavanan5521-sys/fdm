/* ════════════════════════════════════════════════════════════
   Freedom Discovery — Testimonials gallery lightbox
   (kept out of the HTML so the Content-Security-Policy can
   forbid inline scripts)
   ════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var marquee = document.getElementById('galleryMarquee');
  var lb = document.getElementById('gLightbox');
  if (!marquee || !lb) return;

  var lbImg = document.getElementById('gLbImg');
  var btnClose = document.getElementById('gLbClose');
  var btnPrev = document.getElementById('gLbPrev');
  var btnNext = document.getElementById('gLbNext');

  // Build a unique, ordered list of real photos (skip the duplicated clones).
  var sources = [];
  marquee.querySelectorAll('.g-pic:not([aria-hidden="true"]) img').forEach(function (img) {
    if (sources.indexOf(img.getAttribute('src')) === -1) sources.push(img.getAttribute('src'));
  });
  var current = 0;

  function show(i) {
    current = (i + sources.length) % sources.length;
    lbImg.setAttribute('src', sources[current]);
  }
  function open(src) {
    var i = sources.indexOf(src);
    show(i === -1 ? 0 : i);
    lb.classList.add('open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
  function close() {
    lb.classList.remove('open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  marquee.addEventListener('click', function (e) {
    var pic = e.target.closest('.g-pic');
    if (!pic) return;
    var img = pic.querySelector('img');
    if (img) open(img.getAttribute('src'));
  });
  btnClose.addEventListener('click', close);
  btnPrev.addEventListener('click', function () { show(current - 1); });
  btnNext.addEventListener('click', function () { show(current + 1); });
  lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
  document.addEventListener('keydown', function (e) {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowLeft') show(current - 1);
    else if (e.key === 'ArrowRight') show(current + 1);
  });
})();
