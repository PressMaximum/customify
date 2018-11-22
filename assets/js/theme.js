class Customify {
	/**
	 * Contructor medthod.
	 */
	constructor() {
		this.options = {
			menuToggleDuration: 300
		};
		this.init();
		this.menuSidebarState = "closed";
		this.isPreviewing = document.body.classList.contains(
			"customize-previewing"
		);
	}

	jQueryEvents() {
		var that = this;
		jQuery(document).ready(function($) {});
	}

	initClosest() {
		/**
		 * matches() pollyfil
		 * @see https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill
		 */
		if (!Element.prototype.matches) {
			Element.prototype.matches =
				Element.prototype.msMatchesSelector ||
				Element.prototype.webkitMatchesSelector;
		}

		/**
		 * closest() pollyfil
		 * @see https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill
		 */
		if (!Element.prototype.closest) {
			Element.prototype.closest = function(s) {
				var el = this;
				if (!document.documentElement.contains(el)) {
					return null;
				}
				do {
					if (el.matches(s)) {
						return el;
					}
					el = el.parentElement || el.parentNode;
				} while (el !== null && el.nodeType === 1);
				return null;
			};
		}
	}

	/**
	 * Add body class to check touch screen.
	 */
	checkTouchScreen() {
		if ("ontouchstart" in document.documentElement) {
			document.body.classList.add("ontouch-screen");
		} else {
			document.body.classList.add("not-touch-screen");
		}
	}

	/**
	 * Check if current mobile viewing.
	 *
	 * @return bool
	 */
	isMobile() {
		if (
			navigator.userAgent.match(/Android/i) ||
			navigator.userAgent.match(/webOS/i) ||
			navigator.userAgent.match(/iPhone/i) ||
			navigator.userAgent.match(/iPad/i) ||
			navigator.userAgent.match(/iPod/i) ||
			navigator.userAgent.match(/BlackBerry/i) ||
			navigator.userAgent.match(/Windows Phone/i)
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Init mobile sidebar.
	 *
	 * @todo Move menu sidebar to body.
	 * @todo Add events to menu buttons.
	 */
	initMenuSidebar() {
		if (document.body.classList.contains("menu_sidebar_dropdown")) {
			// $( '#header-menu-sidebar' ).insertAfter( "#masthead" );
		} else {
			let menuSidebar = document.getElementById("header-menu-sidebar");
			if (menuSidebar) {
				document.body.append(menuSidebar);
			}
		}

		document.addEventListener(
			"customize_section_opened",
			function(e) {
				if (e.detail === "header_sidebar") {
					this.toggleMenuSidebar(false);
				}
			}.bind(this)
		);

		let menuMobileToggleButtons = document.querySelectorAll(
			".menu-mobile-toggle"
		);
		/**
		 * When click to toggle buttons.
		 */
		this.addEvent(
			menuMobileToggleButtons,
			"click",
			function(e) {
				e.preventDefault();
				this.toggleMenuSidebar();
			}.bind(this)
		);

		let closeButtons = document.querySelectorAll(
			"#header-menu-sidebar .close-panel, .close-sidebar-panel"
		);

		/**
		 * When click close buttons.
		 */
		this.addEvent(
			closeButtons,
			"click",
			function(e) {
				e.preventDefault();
				this.closeMenuSidebar();
			}.bind(this)
		);

		/**
		 * When click to ouside of menu sidebar.
		 */
		this.addEvent(
			document,
			"click",
			function(e) {
				if (document.body.classList.contains("is-menu-sidebar")) {
					let menuSidebar = document.getElementById(
						"header-menu-sidebar"
					);
					var buttons = document.querySelectorAll(
						".menu-mobile-toggle"
					);
					let outside = false;
					// If the click happened inside the the container, bail
					if (
						!e.target.closest("#header-menu-sidebar") &&
						e.target !== menuSidebar
					) {
						// Outside menu sidebar.
						outside = true;
					}

					// Check if not click to menu toggle buttons.
					let onButton = false;
					if (buttons.length) {
						for (let i = 0; i < buttons.length; i++) {
							// If click on toggle button.
							if (
								e.target.closest(".menu-mobile-toggle") ||
								e.target === buttons[i]
							) {
								onButton = true;
							}
						}
					}

					if (outside && !onButton) {
						this.closeMenuSidebar();
					}
				}
			}.bind(this)
		);

		this.addEvent(
			document,
			"keyup",
			function(e) {
				if (e.keyCode === 27) {
					this.closeMenuSidebar();
				}
			}.bind(this)
		);
	}

	/**
	 * Init mobile search form
	 *
	 * @todo Need check
	 */
	initMobieSearchForm() {
		let mobileSearchForm = document.querySelector(".search-form--mobile");
		if (mobileSearchForm) {
			mobileSearchForm.classList.add(
				"mobile-search-form-sidebar menu-sidebar-panel"
			);
			mobileSearchForm.classList.remove("hide-on-mobile hide-on-tablet");
			document.body.prepend(mobileSearchForm);
		}
	}

	toggleMobileSubmenu(e) {
		e.preventDefault();
		var that = this;
		let li = e.target.closest("li");
		let firstSubmenu = li.querySelectorAll(
			":scope  > .sub-menu, .sub-lv-0"
		);

		if (!li.classList.contains("open-sub")) {
			// Show the sub menu.
			li.classList.add("open-sub");
			if (firstSubmenu.length) {
				for (let i = 0; i < firstSubmenu.length; i++) {
					that.slideDown(
						firstSubmenu[i],
						this.options.menuToggleDuration,
						function() {
							li.classList.add("open-sub");
						}
					);
				}
			}
		} else {
			// Hide the sub menu.
			if (firstSubmenu.length) {
				for (let i = 0; i < firstSubmenu.length; i++) {
					that.slideUp(
						firstSubmenu[i],
						this.options.menuToggleDuration,
						function() {
							li.classList.remove("open-sub");
						}
					);
				}
			}
		}
	}

	/**
	 * Add events listener for mobile toggle button.
	 *
	 * @param Element toggleIcon
	 */
	toggleMobileSubmenuEvents(toggleIcon) {
		toggleIcon.addEventListener(
			"click",
			this.toggleMobileSubmenu.bind(this)
		);
	}

	/**
	 * Inital mobile submenu.
	 */
	initMobileSubMenu() {
		let menuChildren = document.querySelectorAll(
			"#header-menu-sidebar .nav-menu-mobile .menu-item-has-children"
		);
		//console.log( 'menuChildren', menuChildren );
		if (menuChildren.length) {
			for (let i = 0; i < menuChildren.length; i++) {
				let child = menuChildren[i];
				if (!child.classList.contains("toggle--added")) {
					child.classList.add("toggle--added");

					let fistLink = child.querySelector(":scope > a");
					let d = fistLink.cloneNode(true);

					if (this.isPreviewing) {
					}

					let toggleButton = document.createElement("span");
					toggleButton.classList.add("nav-toggle-icon");
					toggleButton.innerHTML = '<i class="nav-icon-angle"></i>';

					fistLink.parentNode.insertBefore(toggleButton, fistLink);
					let submenu = child.querySelector(":scope > .sub-menu");
					submenu.prepend(d);

					let firstSubmenu = child.querySelectorAll(
						":scope  > .sub-menu, .sub-lv-0"
					);
					if (firstSubmenu.length) {
						for (let i = 0; i < firstSubmenu.length; i++) {
							this.slideUp(firstSubmenu[i], 0);
						}
					}

					let dWrapper = document.createElement("li");
					d.parentNode.appendChild(dWrapper);
					dWrapper.appendChild(d);

					this.toggleMobileSubmenuEvents(toggleButton);
				}
			}
		}
	}

	/**
	 * SideUp
	 *
	 * @param Element element
	 * @param number duration
	 * @param function callBack
	 */
	slideUp(element, duration, callBack) {
		if (typeof duration !== "number") {
			duration = 0;
		}

		// if ( element._sideUpTimeOut ) {
		// 	clearTimeout( element._sideUpTimeOut );
		// 	element._sideUpTimeOut = false;
		// }

		if (element._slideDownTimeOut) {
			clearTimeout(element._slideDownTimeOut);
			element._slideDownTimeOut = false;
		}

		// Get orignal height.
		let offset = element.getBoundingClientRect();
		// Back to default.
		element.style.overflow = "hidden";
		element.style.height = offset.height + "px";
		element.style.transition = "height " + duration + "ms linear";
		setTimeout(function() {
			element.style.height = "0px";
		}, 20);

		element._sideUpTimeOut = setTimeout(function() {
			element.style.transition = "";
			if (typeof callBack === "function") {
				callBack.call(this);
			}
		}, duration + 20);
	}

	/**
	 *
	 * @param Element element
	 * @param number duration
	 * @param function callBack
	 */
	slideDown(element, duration, callBack) {
		if (typeof duration !== "number") {
			duration = 0;
		}

		if (element._sideUpTimeOut) {
			clearTimeout(element._sideUpTimeOut);
			element._sideUpTimeOut = false;
		}

		// if ( element._slideDownTimeOut ) {
		// 	clearTimeout( element._slideDownTimeOut );
		// 	element._slideDownTimeOut = false;
		// }

		// Reset element styling to get orignal height.
		element.style.height = "initial";
		element.style.overflow = "initial";
		element.style.transition = "";
		// Get orignal height.
		let offset = element.getBoundingClientRect();
		// Back to default.
		element.style.height = "0px";
		element.style.overflow = "hidden";
		element.style.transition = "height " + duration + "ms linear";
		setTimeout(function() {
			element.style.height = offset.height + "px";
		}, 50);

		element._sideUpTimeOut = setTimeout(function() {
			element.style.height = "";
			element.style.overflow = "";
			element.style.transition = "";
			if (typeof callBack === "function") {
				callBack.call(this);
			}
		}, duration);
	}

	insertMenuOverlayClass() {
		var navMobile = document.querySelector(".nav-menu-mobile");
		if (navMobile) {
			if (
				document.body.classList.contains("menu_sidebar_slide_overlay")
			) {
				navMobile.classList.add("nav-menu-overlay");
			} else {
				navMobile.classList.remove("nav-menu-overlay");
			}
		}
	}

	setupMobileItemAnimations(element) {
		let h = window.height;
		if (typeof element === "undefined") {
			element = document.getElementById("header-menu-sidebar");
		}

		var t = 0.2;
		var index = 0;
		var itemsInner = element.querySelectorAll(".item--inner");
		if (itemsInner.length) {
			for (let i = 0; i < itemsInner.length; i++) {
				index++;
				itemsInner[i].style.transitionDelay = index * t + "s";
			}
		}
	}

	/**
	 * Toogle Element class name.
	 *
	 * @param Element element
	 * @param string className
	 */
	toggleClass(element, className) {
		if (element instanceof NodeList) {
			for (let i = 0; i < element.length; i++) {
				if (element[i].classList.contains(className)) {
					element[i].classList.remove(className);
				} else {
					element[i].classList.add(className);
				}
			}
		} else if (element instanceof Node || element instanceof Element) {
			if (element.classList.contains(className)) {
				element.classList.remove(className);
			} else {
				element.classList.add(className);
			}
		}
	}

	/**
	 * Add class to element.
	 *
	 * @param Element element
	 * @param string className
	 */
	addClass(element, className) {
		if (element instanceof NodeList) {
			for (let i = 0; i < element.length; i++) {
				element[i].classList.add(className);
			}
		} else if (element instanceof Node || element instanceof Element) {
			element.classList.add(className);
		}
	}

	/**
	 * Remove class name from element.
	 *
	 * @param Element element
	 * @param string className
	 */
	removeClass(element, className) {
		// Split each class by space.
		let classes = className.split(" ");
		if (element instanceof NodeList) {
			for (let i = 0; i < element.length; i++) {
				for (let j = 0; j < classes.length; j++) {
					element[i].classList.remove(classes[j]);
				}
			}
		} else if (element instanceof Node || element instanceof Element) {
			for (let j = 0; j < classes.length; j++) {
				element.classList.remove(classes[j]);
			}
		}
	}

	/**
	 * Add event handle to elements.
	 *
	 * @param Element element
	 * @param string event
	 * @param function callBack
	 */
	addEvent(element, event, callBack) {
		if (element instanceof NodeList) {
			for (let i = 0; i < element.length; i++) {
				element[i].addEventListener(event, callBack);
			}
		} else if (element instanceof Node || element instanceof Element) {
			element.addEventListener(event, callBack);
		}
	}

	/**
	 * Close menu sidebar.
	 */
	closeMenuSidebar() {
		document.body.classList.add("hiding-header-menu-sidebar");
		document.body.classList.remove("is-menu-sidebar");
		let toggleButtons = document.querySelectorAll(
			".menu-mobile-toggle, .menu-mobile-toggle .hamburger"
		);
		this.removeClass(toggleButtons, "is-active");

		/**
		 * For dropdown sidebar.
		 */
		if (document.body.classList.contains("menu_sidebar_dropdown")) {
			this.removeClass(document.body, "hiding-header-menu-sidebar");
			let menuSidebar = document.getElementById("header-menu-sidebar");
			let menuSidebarInner = document.getElementById(
				"header-menu-sidebar-inner"
			);
			let offset = menuSidebarInner.getBoundingClientRect();
			var h = offset.height;

			this.slideUp(
				menuSidebar,
				300,
				function() {
					menuSidebar.style.height = 0;
					menuSidebar.style.display = "block";
				}.bind(this)
			);
		} else {
			// Else slide sidebar.
			setTimeout(
				function() {
					this.removeClass(
						document.body,
						"hiding-header-menu-sidebar"
					);
				}.bind(this),
				1000
			);
		}
	}

	/**
	 * Toggle menu sidebar.
	 *
	 * @param bool open use animation or not.
	 */
	toggleMenuSidebar(toggle) {
		if (typeof toggle === "undefined") {
			toggle = true;
		}

		document.body.classList.remove("hiding-header-menu-sidebar");

		if (!toggle) {
			document.body.classList.add("is-menu-sidebar");
		} else {
			this.toggleClass(document.body, "is-menu-sidebar");
		}

		if (document.body.classList.contains("menu_sidebar_dropdown")) {
			let buttons = document.querySelectorAll(
				".menu-mobile-toggle, .menu-mobile-toggle .hamburger"
			);
			if (toggle) {
				this.toggleClass(buttons, "is-active");
			} else {
				this.addClass(buttons, "is-active");
			}

			if (document.body.classList.contains("is-menu-sidebar")) {
				let menuSidebar = document.getElementById(
					"header-menu-sidebar"
				);
				let menuSidebarInner = document.getElementById(
					"header-menu-sidebar-inner"
				);
				let offset = menuSidebarInner.getBoundingClientRect();
				var h = offset.height;

				this.slideDown(menuSidebar, 300, function() {
					menuSidebar.style.height = h + "px";
				});
			} else {
				if (toggle) {
					this.closeMenuSidebar();
				}
			}
		}
	}

	/**
	 * Auto align search form.
	 */
	searchFormAutoAlign(){
		let searchItems = document.querySelectorAll(".header-search_icon-item");
		var w = window.innerWidth;

		for (let i = 0; i < searchItems.length; i++) {
			let container = searchItems[i];
			let button = container.querySelector( '.search-icon' );
			let buttonOffset = button.getBoundingClientRect();
			this.removeClass( container, 'search-right search-left' );
			if ( buttonOffset.left > w / 2 ) {
				this.removeClass( container, 'search-right' );
				this.addClass( container, 'search-left' );
			} else {
				this.removeClass( container, 'search-left' );
				this.addClass( container, 'search-right' );
			}
		}
	}

	initSearchForm() {
		let searchItems = document.querySelectorAll(".header-search_icon-item");
		for (let i = 0; i < searchItems.length; i++) {
			let container = searchItems[i];
			this.removeClass(container, "active");
			let icon = container.querySelector(".search-icon");

			/**
			 * Add event handle when click to icon.
			 */
			this.addEvent(
				icon,
				"click",
				function(e) {
					e.preventDefault();
					this.toggleClass(container, "active");
					let inputField = container.querySelector(".search-field");
					if (!container.classList.contains("active")) {
						inputField.blur();
					} else {
						inputField.focus();
					}
				}.bind(this)
			);

			/**
			 * When click outside search form.
			 */
			this.addEvent(document, "click", ( function(e) {
				// if the target of the click isn't the container nor a descendant of the container
				if (
					! ( container === e.target ) &&
					! container.contains(e.target)
				) {
					this.removeClass( container, 'active' );
				}

			} ).bind( this ) );

		}

		this.searchFormAutoAlign();

	}

	/**
	 * Responsive table.
	 */
	responsiveTable(){
		let tables = document.querySelectorAll( ".entry-content table" );
		for( let i = 0; i < tables. length; i ++ ) {
			if ( ! tables[ i ].parentNode.classList.contains( 'table-wrapper' ) ) {
				// Wrap table by each div.table-wrapper
				let dWrapper = document.createElement("div");
				dWrapper.classList.add( 'table-wrapper');
				tables[ i ].parentNode.replaceChild( dWrapper, tables[ i ] );
				dWrapper.appendChild( tables[ i ] );
			}
		}
	}

	/**
	 * Inittial
	 */
	init() {
		this.initClosest();
		this.checkTouchScreen();
		this.initMobieSearchForm();
		this.initMobileSubMenu();
		this.insertMenuOverlayClass();
		this.setupMobileItemAnimations();
		this.initMenuSidebar();
		this.initSearchForm();
		this.responsiveTable();

		/**
		 * Add action when Header Panel rendered by customizer.
		 */
		document.addEventListener(
			"header_builder_panel_changed",
			function() {
				this.initMobileSubMenu();
				this.insertMenuOverlayClass();
			}.bind(this)
		);
		// Add actions when window resize.
		var tf;
		window.addEventListener(
			"resize",
			function() {
				// Resetup mobile animations
				this.setupMobileItemAnimations();

				// Resetup search form alignmenet.
				this.removeClass( document.querySelectorAll( '.header-search_icon-item' ), 'active' );
				if (tf) {
					clearTimeout(tf);
				}
				tf = setTimeout( this.searchFormAutoAlign.bind( this ) , 100);

			}.bind(this)
		);

		
		document.addEventListener(
			"selective-refresh-content-rendered",
			function( e ) {
				if ( e.detail ===  'customify_customize_render_header' ) {
					this.initSearchForm();
				}
				
			}.bind(this)
		);



	}
}

new Customify();

function customify_is_mobile() {
	return Customify.isMobile();
}

//----------------------------------------------------

jQuery(document).ready(function($) {

	$("#page").fitVids();

});

/**
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */
(function() {
	var container, button, menu, links, i, len;

	container = document.getElementById("site-navigation-main-desktop");
	if (!container) {
		return;
	}

	menu = container.getElementsByTagName("ul")[0];
	// Hide menu toggle button if menu is empty and return early.
	if ("undefined" === typeof menu) {
		return;
	}

	menu.setAttribute("aria-expanded", "false");
	if (-1 === menu.className.indexOf("nav-menu")) {
		menu.className += " nav-menu";
	}

	// Get all the link elements within the menu.
	links = menu.getElementsByTagName("a");

	// Each time a menu link is focused or blurred, toggle focus.
	for (i = 0, len = links.length; i < len; i++) {
		links[i].addEventListener("focus", toggleFocus, true);
		links[i].addEventListener("blur", toggleFocus, true);
	}

	/**
	 * Sets or removes .focus class on an element.
	 */
	function toggleFocus() {
		var self = this;

		// Move up through the ancestors of the current link until we hit .nav-menu.
		while (-1 === self.className.indexOf("nav-menu")) {
			// On li elements toggle the class .focus.
			if ("li" === self.tagName.toLowerCase()) {
				if (-1 !== self.className.indexOf("focus")) {
					self.className = self.className.replace(" focus", "");
				} else {
					self.className += " focus";
				}
			}

			self = self.parentElement;
		}
	}

	/**
	 * Toggles `focus` class to allow submenu access on tablets.
	 */
	(function(container) {
		var touchStartFn,
			i,
			parentLink = container.querySelectorAll(
				".menu-item-has-children > a, .page_item_has_children > a"
			);

		if ("ontouchstart" in window) {
			touchStartFn = function(e) {
				var menuItem = this.parentNode,
					i;

				if (!menuItem.classList.contains("focus")) {
					e.preventDefault();
					for (i = 0; i < menuItem.parentNode.children.length; ++i) {
						if (menuItem === menuItem.parentNode.children[i]) {
							continue;
						}
						menuItem.parentNode.children[i].classList.remove(
							"focus"
						);
					}
					menuItem.classList.add("focus");
				} else {
					menuItem.classList.remove("focus");
				}
			};

			for (i = 0; i < parentLink.length; ++i) {
				parentLink[i].addEventListener(
					"touchstart",
					touchStartFn,
					false
				);
			}
		}
	})(container);
})();

/**
 *
 * Helps with accessibility for keyboard only users.
 *
 * Learn more: https://git.io/vWdr2
 */
(function() {
	var isIe = /(trident|msie)/i.test(navigator.userAgent);

	if (isIe && document.getElementById && window.addEventListener) {
		window.addEventListener(
			"hashchange",
			function() {
				var id = location.hash.substring(1),
					element;

				if (!/^[A-z0-9_-]+$/.test(id)) {
					return;
				}

				element = document.getElementById(id);

				if (element) {
					if (
						!/^(?:a|select|input|button|textarea)$/i.test(
							element.tagName
						)
					) {
						element.tabIndex = -1;
					}

					element.focus();
				}
			},
			false
		);
	}
})();
