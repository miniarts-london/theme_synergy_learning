// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Site home: log in form, scroll reveal, video and the catalogue link.
 *
 * @module     theme_synergylearning/frontpage
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Validation, password toggle, Caps Lock warning and a loading state on submit.
 *
 * @param {HTMLFormElement} form
 */
const setupLoginForm = (form) => {
    const username = form.querySelector('#sl-username');
    const password = form.querySelector('#sl-password');

    const check = (input) => {
        const ok = input.value.trim() !== '';
        input.classList.toggle('is-invalid', !ok);
        input.setAttribute('aria-invalid', ok ? 'false' : 'true');
        return ok;
    };

    // Don't shout at people before they've typed anything.
    for (const input of [username, password]) {
        input.addEventListener('input', () => {
            input.dataset.touched = 1;
            if (input.classList.contains('is-invalid')) {
                check(input);
            }
        });
        input.addEventListener('blur', () => input.dataset.touched && check(input));
    }

    form.addEventListener('submit', (e) => {
        // Check both, so both get marked, then focus the first one that's wrong.
        const errors = [username, password].filter((input) => !check(input));
        if (errors.length) {
            e.preventDefault();
            errors[0].focus();
            return;
        }

        const button = form.querySelector('[data-region="sl-login-submit"]');
        button.querySelector('.sl-btn-icon')?.setAttribute('hidden', '');
        button.insertAdjacentHTML('afterbegin', '<span class="spinner-border" aria-hidden="true"></span>');
        button.querySelector('[data-region="sl-login-label"]').textContent = form.dataset.loggingin;
        button.setAttribute('aria-busy', 'true');
        // Disabling it straight away would cancel the submit.
        setTimeout(() => {
            button.disabled = true;
        });
    });

    const toggle = form.querySelector('[data-action="sl-toggle-password"]');
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const showing = password.type === 'password';
        password.type = showing ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', showing);
        toggle.querySelector('[data-region="sl-toggle-label"]').textContent =
            showing ? form.dataset.hidepassword : form.dataset.showpassword;
        password.focus();
    });

    const capsWarning = form.querySelector('#sl-capslock');
    const updateCaps = (e) => {
        capsWarning.hidden = !e.getModifierState?.('CapsLock');
    };
    password.addEventListener('keydown', updateCaps);
    password.addEventListener('keyup', updateCaps);
    password.addEventListener('blur', () => {
        capsWarning.hidden = true;
    });
};

/**
 * Fade the sections and cards in as they come into view.
 *
 * @param {HTMLElement} root
 */
const setupReveal = (root) => {
    if (prefersReducedMotion() || !window.IntersectionObserver) {
        return;
    }

    const cards = root.querySelectorAll('.sl-catalogue__grid [data-sl-reveal]');
    cards.forEach((card, i) => card.style.setProperty('--sl-reveal-index', i));

    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        }
    }, {threshold: 0.15});
    root.querySelectorAll('[data-sl-reveal]').forEach((el) => observer.observe(el));

    // Content is only hidden from here on, once we know the observer will show it again.
    root.classList.add('sl-reveal-ready');
};

/**
 * Click-to-load video, so nothing is downloaded until someone presses play.
 *
 * @param {HTMLElement} frame
 */
const setupVideo = (frame) => {
    const playButton = frame.querySelector('[data-action="sl-play-video"]');

    playButton.addEventListener('click', () => {
        const video = document.createElement('video');
        video.controls = true;
        video.playsInline = true;
        video.poster = frame.querySelector('img')?.currentSrc || '';
        video.setAttribute('aria-label', frame.dataset.title);
        const source = document.createElement('source');
        source.src = frame.dataset.src;
        source.type = 'video/mp4';
        video.append(source);

        // No video file has been supplied yet, so expect this to fail for now.
        source.addEventListener('error', () => {
            const note = document.createElement('div');
            note.className = 'sl-video__fallback';
            note.setAttribute('role', 'status');
            note.innerHTML = '<strong></strong><span></span>';
            note.firstChild.textContent = frame.dataset.unavailable;
            note.lastChild.textContent = frame.dataset.unavailabledesc;
            frame.append(note);
        });

        playButton.replaceWith(video);
        video.focus();
        video.play().catch(() => {
            // Autoplay was blocked; the controls are there.
        });
    }, {once: true});
};

/**
 * "Course catalogue" in the top menu: scroll down instead of reloading when we're already on the site home.
 *
 * @param {HTMLElement} section
 */
const setupCatalogueLink = (section) => {
    const isHome = (url) => url.pathname.replace(/index\.php$/, '') === location.pathname.replace(/index\.php$/, '');

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href*="#catalogue"]');
        // Leave new-tab clicks and the mobile drawer (which needs to close) alone.
        if (!link || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || link.closest('.drawer')) {
            return;
        }
        if (!isHome(new URL(link.href))) {
            return;
        }

        e.preventDefault();
        section.scrollIntoView({behavior: prefersReducedMotion() ? 'auto' : 'smooth'});
        const heading = section.querySelector('h2');
        heading.tabIndex = -1;
        heading.focus({preventScroll: true});
        history.replaceState(null, '', '#catalogue');
    });
};

export const init = () => {
    const root = document.querySelector('[data-region="sl-landing"]');
    if (!root) {
        return;
    }

    // Moodle's skip link points past the landing sections (and the log in form), so point it at them instead.
    document.querySelectorAll('a[href="#maincontent"]').forEach((link) => {
        link.href = '#' + root.id;
    });

    // Signed-in users get a welcome panel instead of the form.
    const form = root.querySelector('[data-region="sl-login"]');
    if (form) {
        setupLoginForm(form);
    }
    root.querySelectorAll('[data-region="sl-video"]').forEach(setupVideo);

    // The catalogue section isn't there when no categories exist yet.
    const catalogue = root.querySelector('#catalogue');
    if (catalogue) {
        setupCatalogueLink(catalogue);
    }
    setupReveal(root);
};
