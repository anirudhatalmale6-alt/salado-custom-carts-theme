/**
 * Salado Custom Carts - front end behaviour.
 * Mobile menu toggle only. No framework, no jQuery.
 */
(function () {
	'use strict';

	var burger = document.querySelector('.scc-burger');
	var nav = document.getElementById('scc-nav');

	if (burger && nav) {
		burger.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		// Close the menu when a link is tapped, so the page below is visible.
		nav.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				nav.classList.remove('is-open');
				burger.setAttribute('aria-expanded', 'false');
			}
		});

		// Close on Escape.
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && nav.classList.contains('is-open')) {
				nav.classList.remove('is-open');
				burger.setAttribute('aria-expanded', 'false');
				burger.focus();
			}
		});
	}
})();
