/* ════════════════════════════════════════════════════════════
   Freedom Discovery — Homepage interactions
   ════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  /* ─── Current year ─── */
  var yr = document.getElementById('currentYear');
  if (yr) yr.textContent = new Date().getFullYear();

  /* ─── Navbar: dynamic island shrink + mobile menu ─── */
  var navbar = document.getElementById('navbar');
  var SHRINK_AT = 60, EXPAND_AT = 10, ticking = false;

  function updateNav() {
    var y = window.scrollY;
    if (!navbar.classList.contains('shrunk') && y > SHRINK_AT) navbar.classList.add('shrunk');
    else if (navbar.classList.contains('shrunk') && y < EXPAND_AT) navbar.classList.remove('shrunk');
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { ticking = true; requestAnimationFrame(updateNav); }
  }, { passive: true });
  updateNav();

  var hamburger = document.getElementById('hamburger');
  var mobileMenu = document.getElementById('mobileMenu');
  function setMenu(open) {
    navbar.classList.toggle('menu-open', open);
    hamburger.setAttribute('aria-expanded', open);
  }
  hamburger.addEventListener('click', function () { setMenu(!navbar.classList.contains('menu-open')); });
  mobileMenu.addEventListener('click', function (e) { if (e.target.tagName === 'A') setMenu(false); });
  document.addEventListener('click', function (e) { if (!navbar.contains(e.target)) setMenu(false); });

  /* ─── Hero: word-stagger headline ─── */
  var title = document.getElementById('heroTitle');
  if (title) {
    var accentSet = {};
    (title.dataset.accent || '').toLowerCase().split(/\s+/).forEach(function (w) {
      if (w) accentSet[w.replace(/[^a-z0-9]/g, '')] = true;
    });
    var words = title.textContent.trim().split(/\s+/);
    var step = words.length > 8 ? 0.05 : 0.09;
    title.innerHTML = words.map(function (w, i) {
      var key = w.toLowerCase().replace(/[^a-z0-9]/g, '');
      var accent = accentSet[key] ? ' accent' : '';
      return '<span class="word' + accent + '" style="animation-delay:' + (i * step) + 's">' + w + '</span>';
    }).join(' ');
  }

  /* ─── Hero: typewriter sub (types once, then stops) ─── */
  (function () {
    var el = document.getElementById('twEl');
    if (!el) return;
    var text = 'The Fine Artisans of Learning Solution Excellency.';
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { el.textContent = text; el.classList.add('tw-done'); return; }
    var i = 0;
    function tick() {
      el.textContent = text.slice(0, i++);
      if (i <= text.length) setTimeout(tick, 26);
      else el.classList.add('tw-done');
    }
    setTimeout(tick, 900);
  })();

  /* ─── Number count-up ─── */
  function countUp(el, target) {
    var start = performance.now(), dur = 1900;
    (function frame(now) {
      var p = Math.min((now - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.floor(eased * target).toLocaleString();
      if (p < 1) requestAnimationFrame(frame);
      else el.textContent = target.toLocaleString();
    })(start);
  }

  /* ─── Stagger reveal delays inside grids ─── */
  document.querySelectorAll('.why-grid, .services-grid, .pillars-grid, .stats-grid').forEach(function (group) {
    group.querySelectorAll('.reveal').forEach(function (el, i) {
      el.style.transitionDelay = (i * 0.08) + 's';
    });
  });

  /* ─── IntersectionObserver: reveals + counters ─── */
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      el.classList.add('in');
      el.querySelectorAll('.cnt').forEach(function (n) { countUp(n, +n.dataset.n); });
      io.unobserve(el);
    });
  }, { threshold: 0.15 });
  document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });

  /* ─── Timeline: line fill + dot activation ─── */
  (function () {
    var track = document.querySelector('.timeline-track');
    if (!track) return;
    var items = track.querySelectorAll('.timeline-item');
    var tio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('in');
        var revealed = track.querySelectorAll('.timeline-item.in').length;
        var pct = ((revealed - 1) / (items.length - 1)) * 100;
        track.style.setProperty('--fill', pct + '%');
        tio.unobserve(entry.target);
      });
    }, { threshold: 0.5 });
    items.forEach(function (i) { tio.observe(i); });
  })();

  /* ─── 3D tilt cards (pointer-capable devices only) ─── */
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    document.querySelectorAll('.tilt').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var r = card.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform = 'perspective(820px) rotateY(' + (x * 8) + 'deg) rotateX(' + (-y * 8) + 'deg) translateY(-4px)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.transform = 'perspective(820px) rotateY(0) rotateX(0) translateY(0)';
      });
    });
  }

  /* ─── Magnetic CTA button ─── */
  (function () {
    var wrap = document.getElementById('magWrap');
    var btn = document.getElementById('magBtn');
    if (!wrap || !btn || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    wrap.addEventListener('mousemove', function (e) {
      var r = btn.getBoundingClientRect();
      var dx = (e.clientX - r.left - r.width / 2) * 0.3;
      var dy = (e.clientY - r.top - r.height / 2) * 0.3;
      btn.style.transition = 'background 0.2s ease, box-shadow 0.25s ease';
      btn.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';
    });
    wrap.addEventListener('mouseleave', function () {
      btn.style.transition = 'background 0.2s ease, box-shadow 0.25s ease, transform 0.5s cubic-bezier(.32,.72,.22,1)';
      btn.style.transform = 'translate(0,0)';
    });
  })();

  /* ─── Back to top ─── */
  var toTop = document.getElementById('backToTop');
  if (toTop) {
    window.addEventListener('scroll', function () {
      toTop.classList.toggle('show', window.scrollY > 600);
    }, { passive: true });
    toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  /* ─── Announcement Dynamic-Island Capsule ─── */
  (function () {
    var cap = document.getElementById('cap');
    var pill = document.getElementById('capPill');
    var card = document.getElementById('capCard');
    var poster = document.getElementById('capPoster');
    var closeBtn = document.getElementById('capClose');
    var zoomBtn = document.getElementById('capZoom');
    var lb = document.getElementById('capLightbox');
    var lbImg = document.getElementById('capLbImg');
    var lbClose = document.getElementById('capLbClose');
    if (!cap) return;

    /* Poster lightbox — tap/click any poster to see it full-screen (works on mobile too) */
    function lbOpen(src) {
      if (!lb) return;
      lbImg.src = src || poster.src;
      lb.classList.add('open');
      lb.setAttribute('aria-hidden', 'false');
    }
    function lbClosed() { return !lb || !lb.classList.contains('open'); }
    function closeLb() {
      if (!lb) return;
      lb.classList.remove('open');
      lb.setAttribute('aria-hidden', 'true');
    }
    /* bind every poster slide by class, so adding a poster needs no new JS */
    Array.prototype.forEach.call(cap.querySelectorAll('.cap-poster-btn'), function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var img = btn.querySelector('img');
        lbOpen(img && img.src);
      });
    });
    if (lbClose) lbClose.addEventListener('click', closeLb);
    if (lb) lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });

    var pinned = false; // true after user taps pill open / on touch

    /* Size each card from poster aspect ratio + viewport (narrower per-card when side by side) */
    function sizeCard() {
      var ratio = (poster.naturalWidth && poster.naturalHeight)
        ? poster.naturalWidth / poster.naturalHeight
        : 0.706; // fallback portrait ratio
      var twoUp = window.innerWidth > 760; // cards sit side by side
      var slides = card.querySelectorAll('.cap-slide').length || 1;
      var maxH = Math.min(window.innerHeight * (twoUp ? 0.68 : 0.42), twoUp ? 560 : 460);
      var maxW = twoUp
        ? Math.min(300, (window.innerWidth - 64 - 14 * (slides - 1)) / slides)
        : Math.min(window.innerWidth - 32, 360);
      var w = maxW, h = w / ratio;
      if (h > maxH) { h = maxH; w = h * ratio; }
      cap.style.setProperty('--cap-w', Math.round(w) + 'px');
      cap.style.setProperty('--cap-h', Math.round(h) + 'px');
    }

    function setState(state, animate) {
      cap.classList.toggle('cap-anim', !!animate);
      cap.setAttribute('data-state', state);
      if (animate) {
        setTimeout(function () { cap.classList.remove('cap-anim'); }, 480);
      }
    }

    function expand(animate) { sizeCard(); setState('card', animate); }
    function collapse(animate) { setState('pill', animate); }

    function dismiss() {
      pinned = false;
      collapse(true);
    }

    var isTouch = window.matchMedia('(hover: none)').matches;

    /* First paint — show the poster on load/refresh, then tuck into the pill.
       Hover (or tap on touch) re-opens it. */
    var inited = false;
    // Auto-pop the poster only on the homepage; elsewhere it stays as the pill.
    var p = location.pathname;
    var isHome = p === '/' || /\/(index\.html)?$/.test(p);
    function init() {
      if (inited) return;
      inited = true;
      sizeCard();
      if (!isHome) return;       // other pages: keep collapsed, hover/tap still opens
      setState('card', false);   // homepage: show the poster on load/refresh
      setTimeout(function () { collapse(true); }, 3200); // then collapse to the capsule
    }
    if (poster.complete && poster.naturalWidth) init();
    else poster.addEventListener('load', init);
    // safety: run regardless if the image is slow/broken
    setTimeout(init, 1200);

    /* Hover to expand (desktop) */
    if (!isTouch) {
      pill.addEventListener('mouseenter', function () { expand(true); });
      cap.addEventListener('mouseleave', function () { if (!pinned) collapse(true); });
    }

    /* Tap / click pill to open */
    pill.addEventListener('click', function () { pinned = true; expand(true); });

    /* Close controls */
    closeBtn.addEventListener('click', function (e) { e.stopPropagation(); dismiss(); });

    /* Esc closes — the lightbox first, then the capsule */
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (!lbClosed()) { closeLb(); return; }
      if (cap.getAttribute('data-state') === 'card') dismiss();
    });

    /* Outside tap closes (mainly for touch / pinned-open) — but not while the lightbox is open */
    document.addEventListener('click', function (e) {
      if (!lbClosed()) return;
      if (cap.getAttribute('data-state') === 'card' && !cap.contains(e.target)) {
        pinned = false; collapse(true);
      }
    });

    window.addEventListener('resize', function () {
      if (cap.getAttribute('data-state') === 'card') sizeCard();
    }, { passive: true });
  })();

  /* ─── Programme videos — click-to-play facades ───
     Each .video-facade is just a poster + play button; the real player is
     injected only on click, so pages stay light. data-video-src plays a
     self-hosted MP4 with the clean native player (no YouTube branding);
     data-video-id falls back to a YouTube embed. */
  (function () {
    var facades = document.querySelectorAll('.video-facade[data-video-src], .video-facade[data-video-id]');
    if (!facades.length) return;
    Array.prototype.forEach.call(facades, function (btn) {
      btn.addEventListener('click', function () {
        var frame = document.createElement('div');
        frame.className = 'video-facade';
        var src = btn.getAttribute('data-video-src');
        if (src) {
          /* pause any other programme video already playing */
          Array.prototype.forEach.call(document.querySelectorAll('.video-facade video'), function (v) { v.pause(); });
          var video = document.createElement('video');
          video.src = src;
          video.controls = true;
          video.autoplay = true;
          video.playsInline = true;
          video.setAttribute('controlslist', 'nodownload');
          video.setAttribute('aria-label', btn.getAttribute('aria-label') || 'Programme video');
          frame.appendChild(video);
        } else {
          var iframe = document.createElement('iframe');
          iframe.src = 'https://www.youtube-nocookie.com/embed/' + btn.getAttribute('data-video-id') + '?autoplay=1&rel=0';
          iframe.title = btn.getAttribute('aria-label') || 'Programme video';
          iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
          iframe.setAttribute('allowfullscreen', '');
          frame.appendChild(iframe);
        }
        btn.parentNode.replaceChild(frame, btn);
      });
    });
  })();

})();
