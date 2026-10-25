/**
 * TechViet Store — tương tác giao diện.
 */
(function () {
	'use strict';

	var header = document.getElementById('tv-header');
	var toggle = document.querySelector('.tv-menu-toggle');
	var mobileNav = document.getElementById('tv-mobile-nav');
	var backtop = document.querySelector('.tv-backtop');
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------- Menu di động ---------- */
	function setMenu(open) {
		if (!toggle || !mobileNav) {
			return;
		}

		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		toggle.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
		mobileNav.hidden = !open;
		document.body.classList.toggle('tv-nav-open', open);
	}

	if (toggle && mobileNav) {
		toggle.addEventListener('click', function () {
			setMenu(toggle.getAttribute('aria-expanded') !== 'true');
		});

		mobileNav.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				setMenu(false);
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
				setMenu(false);
				toggle.focus();
			}
		});

		document.addEventListener('click', function (event) {
			if (toggle.getAttribute('aria-expanded') !== 'true') {
				return;
			}
			if (!event.target.closest('#tv-mobile-nav') && !event.target.closest('.tv-menu-toggle')) {
				setMenu(false);
			}
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth > 1024) {
				setMenu(false);
			}
		});
	}

	/* ---------- Header khi cuộn + nút lên đầu trang ---------- */
	var ticking = false;

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;

		if (header) {
			header.classList.toggle('is-scrolled', y > 8);
		}

		if (backtop) {
			if (y > 560) {
				backtop.hidden = false;
			} else {
				backtop.hidden = true;
			}
		}

		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if (!ticking) {
				window.requestAnimationFrame(onScroll);
				ticking = true;
			}
		},
		{ passive: true }
	);
	onScroll();

	if (backtop) {
		backtop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
		});
	}

	/* ---------- Hiệu ứng xuất hiện ---------- */
	if (!reduceMotion && 'IntersectionObserver' in window) {
		var targets = document.querySelectorAll(
			'.tv-product-card, .tv-category-card, .tv-service-card, .tv-testimonial, .tv-value-card, .tv-section-head, .tv-about__visual, .tv-about__content, .tv-contact-card'
		);

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						observer.unobserve(entry.target);
					}
				});
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
		);

		targets.forEach(function (el, index) {
			el.classList.add('tv-reveal');
			el.style.transitionDelay = (index % 3) * 60 + 'ms';
			observer.observe(el);
		});
	}
})();
