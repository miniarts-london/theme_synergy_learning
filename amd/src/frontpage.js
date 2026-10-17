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
 * Progressive enhancements for the MyLearning site home.
 *
 * The page works fully without JavaScript; every feature below is optional and isolated,
 * so one failing never prevents the others from running.
 *
 * @module     theme_synergylearning/frontpage
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTORS = {
    landing: '[data-region="sl-landing"]',
    form: '[data-region="sl-login"]',
    submit: '[data-region="sl-login-submit"]',
    submitLabel: '[data-region="sl-login-label"]',
    passwordToggle: '[data-action="sl-toggle-password"]',
    toggleLabel: '[data-region="sl-toggle-label"]',
    reveal: '[data-sl-reveal]',
    video: '[data-region="sl-video"]',
    playVideo: '[data-action="sl-play-video"]',
    catalogue: '#catalogue',
    catalogueLink: 'a[href*="#catalogue"]',
};

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Log in form: inline validation, a show/hide password toggle, a Caps Lock warning and a busy state on submit.
 *
 * @param {HTMLFormElement} form
 */
const initLoginForm = (form) => {
    const username = form.querySelector('#sl-username');
    const password = form.querySelector('#sl-password');
    const fields = [username, password];

    const validate = (field) => {
        const valid = field.value.trim() !== '';
        field.classList.toggle('is-invalid', !valid);
        field.setAttribute('aria-invalid', String(!valid));
        return valid;
    };

    // Validate on blur only once the user has typed, then clear errors live as they are fixed.
    fields.forEach((field) => {
        field.addEventListener('blur', () => {
            if (field.dataset.touched) {
                validate(field);
            }
        });
        field.addEventListener('input', () => {
            field.dataset.touched = 'true';
            if (field.classList.contains('is-invalid')) {
                validate(field);
            }
        });
    });

    form.addEventListener('submit', (e) => {
        const invalid = fields.filter((field) => !validate(field));
        if (invalid.length) {
            e.preventDefault();
            invalid[0].focus();
            return;
        }

        // Valid: let the browser post to /login/index.php, and show that something is happening.
        const submit = form.querySelector(SELECTORS.submit);
        const icon = submit.querySelector('.sl-btn-icon');
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border';
        spinner.setAttribute('aria-hidden', 'true');
        icon?.setAttribute('hidden', '');
        submit.prepend(spinner);
        submit.querySelector(SELECTORS.submitLabel).textContent = form.dataset.loggingin;
        submit.setAttribute('aria-busy', 'true');
        // Disable after the submit event has been dispatched, so the form still sends.
        setTimeout(() => {
            submit.disabled = true;
        }, 0);
    });

    // Show / hide password. The button is hidden in the markup so it never appears without JavaScript.
    const toggle = form.querySelector(SELECTORS.passwordToggle);
    if (toggle) {
        const label = toggle.querySelector(SELECTORS.toggleLabel);
        toggle.hidden = false;
        toggle.addEventListener('click', () => {
            const show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(show));
            label.textContent = show ? form.dataset.hidepassword : form.dataset.showpassword;
            password.focus();
        });
    }

    // Caps Lock warning: a common cause of failed log ins.
    const caps = form.querySelector('#sl-capslock');
    const checkCaps = (e) => {
        if (typeof e.getModifierState === 'function') {
            caps.hidden = !e.getModifierState('CapsLock');
        }
    };
    password.addEventListener('keydown', checkCaps);
    password.addEventListener('keyup', checkCaps);
    password.addEventListener('blur', () => {
        caps.hidden = true;
    });
};

/**
 * Fade sections and cards up as they scroll into view, staggering the catalogue cards.
 *
 * @param {HTMLElement} root
 */
const initReveal = (root) => {
    const items = [...root.querySelectorAll(SELECTORS.reveal)];
    if (!items.length || !('IntersectionObserver' in window) || reducedMotion()) {
        return;
    }

    root.querySelectorAll(`.sl-catalogue__grid ${SELECTORS.reveal}`).forEach((el, i) => {
        el.style.setProperty('--sl-reveal-index', i);
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, {threshold: 0.15});

    items.forEach((el) => observer.observe(el));
    // Only hide content now that it is guaranteed to be revealed again.
    root.classList.add('sl-reveal-ready');
};

/**
 * Video facade: nothing is downloaded until the user asks to play.
 *
 * @param {HTMLElement} frame
 */
const initVideo = (frame) => {
    const button = frame.querySelector(SELECTORS.playVideo);
    if (!button) {
        return;
    }

    button.addEventListener('click', () => {
        const video = document.createElement('video');
        video.controls = true;
        video.playsInline = true;
        video.preload = 'metadata';
        video.poster = frame.querySelector('img')?.currentSrc ?? '';
        video.setAttribute('aria-label', frame.dataset.title);

        const source = document.createElement('source');
        source.src = frame.dataset.src;
        source.type = 'video/mp4';
        video.append(source);

        const showUnavailable = () => {
            if (frame.querySelector('.sl-video__fallback')) {
                return;
            }
            const message = document.createElement('div');
            message.className = 'sl-video__fallback';
            message.setAttribute('role', 'status');
            const title = document.createElement('strong');
            title.textContent = frame.dataset.unavailable;
            const description = document.createElement('span');
            description.textContent = frame.dataset.unavailabledesc;
            message.append(title, description);
            frame.append(message);
        };
        source.addEventListener('error', showUnavailable);
        video.addEventListener('error', showUnavailable);

        button.replaceWith(video);
        video.focus();
        video.play()?.catch(() => {
            // Autoplay can be refused by the browser; the native controls remain available.
        });
    }, {once: true});
};

/**
 * The top menu's "Course catalogue" link scrolls to the catalogue section when it is already on this page.
 *
 * From any other page the link simply opens the site home at #catalogue.
 *
 * @param {HTMLElement} root The landing sections.
 */
const initCatalogueLink = (root) => {
    const section = root.querySelector(SELECTORS.catalogue);
    if (!section) {
        return;
    }
    const samePage = (url) => {
        const path = (p) => p.replace(/index\.php$/, '');
        return url.origin === window.location.origin && path(url.pathname) === path(window.location.pathname);
    };

    document.addEventListener('click', (e) => {
        const link = e.target.closest(SELECTORS.catalogueLink);
        if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
            return;
        }
        // Inside the mobile navigation drawer, let the link navigate normally so the drawer closes.
        if (link.closest('.drawer')) {
            return;
        }
        if (!samePage(new URL(link.href, window.location.href))) {
            return;
        }
        e.preventDefault();
        section.scrollIntoView({behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start'});
        // Move focus to the section heading, so keyboard and screen reader users continue from there.
        const heading = section.querySelector('h2');
        if (heading) {
            heading.setAttribute('tabindex', '-1');
            heading.focus({preventScroll: true});
        }
        window.history.replaceState(null, '', '#catalogue');
    });
};

/**
 * Run a feature without letting an error in it stop the rest.
 *
 * @param {Function} feature
 * @param {...*} args
 */
const safely = (feature, ...args) => {
    try {
        feature(...args);
    } catch (error) {
        window.console.error(error);
    }
};

/**
 * Initialise the site home enhancements.
 */
export const init = () => {
    const root = document.querySelector(SELECTORS.landing);
    if (!root || root.dataset.slInitialised) {
        return;
    }
    root.dataset.slInitialised = 'true';

    // Moodle's "Skip to main content" link targets the regular site home content, which comes after the landing
    // sections. Point it at the landing instead, so keyboard users are not sent past the log in form.
    document.querySelectorAll('a[href="#maincontent"]').forEach((link) => {
        link.setAttribute('href', `#${root.id}`);
    });

    const form = root.querySelector(SELECTORS.form);
    if (form) {
        safely(initLoginForm, form);
    }
    safely(initReveal, root);
    safely(initCatalogueLink, root);
    root.querySelectorAll(SELECTORS.video).forEach((frame) => safely(initVideo, frame));
};
