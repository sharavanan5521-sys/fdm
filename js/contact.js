/* ════════════════════════════════════════════════════════════
   Freedom Discovery — Contact form → WhatsApp
   No backend: the form builds a pre-filled WhatsApp message.
   (kept out of the HTML so the Content-Security-Policy can
   forbid inline scripts)
   ════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var form = document.getElementById('contactForm');
  if (!form) return;
  var PHONE = '60124883300';
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.reportValidity()) return;
    var v = function (id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; };
    var lines = [
      '*New enquiry from freedomdiscovery.net*',
      '',
      'Name: ' + v('name'),
      v('company') ? 'Company: ' + v('company') : '',
      'Email: ' + v('email'),
      v('phone') ? 'Phone: ' + v('phone') : '',
      'Subject: ' + v('subject'),
      '',
      'Message:',
      v('message')
    ].filter(function (l) { return l !== ''; });
    var url = 'https://wa.me/' + PHONE + '?text=' + encodeURIComponent(lines.join('\n'));
    window.open(url, '_blank', 'noopener');
  });
})();
