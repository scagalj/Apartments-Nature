/* Apartments Nature – public website scripts (no dependencies). */
(function () {
    'use strict';

    var $ = function (selector, root) { return (root || document).querySelector(selector); };
    var $$ = function (selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); };

    /* ---------- Header ---------- */

    var header = $('[data-header]');
    if (header) {
        var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    var navToggle = $('[data-nav-toggle]');
    if (navToggle) {
        var setNav = function (open) {
            document.body.classList.toggle('nav-open', open);
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        navToggle.addEventListener('click', function () {
            setNav(!document.body.classList.contains('nav-open'));
        });
        $$('#main-nav a').forEach(function (link) {
            link.addEventListener('click', function () { setNav(false); });
        });
    }

    var langSwitch = $('[data-lang-switch]');
    if (langSwitch) {
        document.addEventListener('click', function (event) {
            if (langSwitch.open && !langSwitch.contains(event.target)) {
                langSwitch.open = false;
            }
        });
    }

    /* ---------- Horizontal sliders (reviews) ---------- */

    $$('[data-slider-prev], [data-slider-next]').forEach(function (button) {
        var track = document.getElementById(button.getAttribute('data-slider-prev') || button.getAttribute('data-slider-next'));
        if (!track) {
            return;
        }
        var direction = button.hasAttribute('data-slider-prev') ? -1 : 1;
        button.addEventListener('click', function () {
            track.scrollBy({ left: direction * track.clientWidth * 0.9, behavior: 'smooth' });
        });
        var update = function () {
            var atStart = track.scrollLeft <= 4;
            var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
            button.disabled = direction < 0 ? atStart : atEnd;
        };
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });

    /* ---------- Date pickers ---------- */

    var parseDate = function (value) {
        var parts = (value || '').split('.');
        if (parts.length !== 3) {
            return null;
        }
        var date = new Date(+parts[2], +parts[1] - 1, +parts[0]);
        return isNaN(date.getTime()) ? null : date;
    };

    var addDays = function (date, days) {
        var copy = new Date(date.getTime());
        copy.setDate(copy.getDate() + days);
        return copy;
    };

    $$('[data-date-range]').forEach(function (form) {
        var checkIn = $('[data-check-in]', form);
        var checkOut = $('[data-check-out]', form);
        if (!checkIn || !checkOut || typeof window.flatpickr !== 'function') {
            return;
        }

        var lang = document.documentElement.lang;
        var options = {
            dateFormat: 'd.m.Y',
            minDate: 'today',
            disableMobile: true,
            locale: (window.flatpickr.l10ns && window.flatpickr.l10ns[lang]) || 'default'
        };
        if (options.locale !== 'default') {
            options.locale = Object.assign({}, options.locale, { firstDayOfWeek: 1 });
        } else {
            options.locale = { firstDayOfWeek: 1 };
        }

        var outPicker;
        var inPicker = window.flatpickr(checkIn, Object.assign({}, options, {
            onChange: function (dates) {
                if (!dates.length) {
                    return;
                }
                var nextDay = addDays(dates[0], 1);
                outPicker.set('minDate', nextDay);
                var current = parseDate(checkOut.value);
                if (!current || current <= dates[0]) {
                    outPicker.setDate(nextDay, false);
                }
                form.dispatchEvent(new Event('datechange'));
                setTimeout(function () { outPicker.open(); }, 50);
            }
        }));

        var initialIn = parseDate(checkIn.value);
        outPicker = window.flatpickr(checkOut, Object.assign({}, options, {
            minDate: initialIn ? addDays(initialIn, 1) : addDays(new Date(), 1),
            onChange: function () { form.dispatchEvent(new Event('datechange')); }
        }));

        // Do not keep dates from the past in the form.
        if (initialIn && initialIn < addDays(new Date(), -1)) {
            inPicker.clear();
            outPicker.clear();
        }
    });

    /* ---------- Live price on the apartment page ---------- */

    var priceForm = $('[data-price-form]');
    if (priceForm) {
        var priceText = $('[data-price-text]', priceForm);
        var priceAmount = $('[data-price-amount]', priceForm);
        var refreshPrice = function () {
            var from = $('[data-check-in]', priceForm).value;
            var to = $('[data-check-out]', priceForm).value;
            if (!from || !to) {
                return;
            }
            var params = new URLSearchParams({
                period_from: from,
                period_to: to,
                apartment_id: $('[data-apartment-id]', priceForm).value,
                guests: $('[data-guests]', priceForm).value
            });
            priceAmount.classList.add('is-loading');
            fetch('/apartment/price?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error(response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    priceText.textContent = data.priceTextTag;
                    priceAmount.textContent = data.priceAmountTag;
                })
                .catch(function () {
                    priceAmount.textContent = '';
                })
                .finally(function () { priceAmount.classList.remove('is-loading'); });
        };
        priceForm.addEventListener('datechange', refreshPrice);
        $('[data-guests]', priceForm).addEventListener('change', refreshPrice);
    }

    /* ---------- Gallery lightbox ---------- */

    var galleryData = $('#gallery-data');
    var lightbox = $('[data-lightbox]');
    if (galleryData && lightbox) {
        var images = JSON.parse(galleryData.textContent || '[]');
        var image = $('[data-lightbox-img]', lightbox);
        var counter = $('[data-lightbox-count]', lightbox);
        var index = 0;
        var lastFocus = null;

        var show = function (i) {
            index = (i + images.length) % images.length;
            image.src = images[index].src;
            image.alt = images[index].alt;
            counter.textContent = (index + 1) + ' / ' + images.length;
            // Preload the next photo.
            new Image().src = images[(index + 1) % images.length].src;
        };
        var open = function (i) {
            lastFocus = document.activeElement;
            show(i);
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
            $('[data-lightbox-close]', lightbox).focus();
        };
        var close = function () {
            lightbox.hidden = true;
            document.body.style.overflow = '';
            if (lastFocus) {
                lastFocus.focus();
            }
        };

        $$('[data-gallery-open]').forEach(function (button) {
            button.addEventListener('click', function () { open(parseInt(button.getAttribute('data-gallery-open'), 10) || 0); });
        });
        $('[data-lightbox-close]', lightbox).addEventListener('click', close);
        $('[data-lightbox-prev]', lightbox).addEventListener('click', function () { show(index - 1); });
        $('[data-lightbox-next]', lightbox).addEventListener('click', function () { show(index + 1); });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox || event.target.classList.contains('lightbox-stage')) {
                close();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (lightbox.hidden) {
                return;
            }
            if (event.key === 'Escape') {
                close();
            } else if (event.key === 'ArrowLeft') {
                show(index - 1);
            } else if (event.key === 'ArrowRight') {
                show(index + 1);
            }
        });

        var touchX = null;
        lightbox.addEventListener('touchstart', function (event) { touchX = event.touches[0].clientX; }, { passive: true });
        lightbox.addEventListener('touchend', function (event) {
            if (touchX === null) {
                return;
            }
            var delta = event.changedTouches[0].clientX - touchX;
            if (Math.abs(delta) > 40) {
                show(index + (delta < 0 ? 1 : -1));
            }
            touchX = null;
        });
    }

    /* ---------- "Show all" amenities ---------- */

    $$('[data-collapsible-toggle]').forEach(function (button) {
        var list = button.previousElementSibling;
        var moreLabel = button.textContent;
        button.addEventListener('click', function () {
            var expanded = list.classList.toggle('is-expanded');
            button.textContent = expanded ? button.getAttribute('data-label-less') : moreLabel;
        });
    });

    /* ---------- Mobile booking bar: hide while the booking form is visible ---------- */

    var bookBar = $('[data-mobile-book-bar]');
    var booking = $('#booking');
    if (bookBar && booking && 'IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            bookBar.classList.toggle('is-hidden', entries[0].isIntersecting);
        }, { threshold: 0.15 }).observe(booking);
    }
})();
