/* ianus IT – ein Skript für alle Seiten: Menü, Leistungen-Untermenü,
   E-Mail-Adressen und Bot-Schutz im Formular.
   Ohne Skript funktioniert die Seite auch: Das Menü steht dann offen da. */
(function () {
  var nav = document.getElementById('hauptnavigation');
  var menuToggle = document.querySelector('.menu-toggle');

  function setMenu(open) {
    if (!menuToggle || !nav) return;
    menuToggle.setAttribute('aria-expanded', String(open));
    nav.classList.toggle('is-open', open);
  }

  if (menuToggle && nav) {
    menuToggle.addEventListener('click', function () {
      setMenu(menuToggle.getAttribute('aria-expanded') !== 'true');
    });
    nav.addEventListener('click', function (event) {
      if (event.target.closest('a')) setMenu(false);
    });
  }

  // Untermenü "Leistungen": öffnet per Klick, schließt per Escape oder Klick daneben
  document.querySelectorAll('.nav__item--sub').forEach(function (item) {
    var button = item.querySelector('.nav__sub-toggle');
    var list = item.querySelector('.nav__sub');
    if (!button || !list) return;
    list.hidden = true;

    function setSub(open) {
      button.setAttribute('aria-expanded', String(open));
      list.hidden = !open;
    }

    button.addEventListener('click', function () {
      setSub(button.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('click', function (event) {
      if (!item.contains(event.target)) setSub(false);
    });
    item.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
        setSub(false);
        button.focus();
      }
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && menuToggle && menuToggle.getAttribute('aria-expanded') === 'true') {
      setMenu(false);
      menuToggle.focus();
    }
  });

  // E-Mail-Adressen erst im Browser zusammensetzen (Schutz vor Spam-Programmen)
  document.querySelectorAll('.js-mail').forEach(function (link) {
    var address = link.getAttribute('data-user') + '@' + link.getAttribute('data-domain');
    link.setAttribute('href', 'mailto:' + address);
    link.textContent = address;
  });

  // Kontaktformular: Zeitmarke für den Bot-Schutz
  var token = document.querySelector('.contact-form input[name="form_token"]');
  if (token) token.value = 'ianus-' + Date.now();
})();
