jQuery(document).ready(function($) {
	if ($.blockUI) {
		$.blockUI.defaults.overlayCSS.backgroundColor = "#FFF";
		$.blockUI.defaults.overlayCSS.opacity = 0.7;
	}

	jQuery(document).on("selective-refresh-content-rendered", function(e, id) {
		if (
			id === "Customify_Builder_Item_WC_Cart__render" ||
			id === "customify_customize_render_header"
		) {
			$(document.body).trigger("wc_fragment_refresh");
		}
	});

	$(document.body).on("added_to_cart", function(event, fragments, cart_hash) {
		$(".item--wc_cart").addClass("cart-active");
		setTimeout(function() {
			$(".item--wc_cart").removeClass("cart-active");
		}, 4000);
	});

	if (Customify_JS.wc_open_cart) {
		$(document.body).trigger("added_to_cart");
	}

	$(document.body).on("wc_cart_button_updated", function(e, button) {
		var p = button.parent();
		if (!button.hasClass("single_add_to_cart_button")) {
			$(".added_to_cart", p).addClass("button");
		}

		var pos = $(".add_to_cart_button", p).data("icon-pos") || "before";
		var icon = $(".add_to_cart_button", p).data("cart-icon") || "";
		var text = "";
		var icon_code = "";
		if (icon) {
			icon_code = '<i class="' + icon + '"></i>';
		}
		if (pos === "after") {
			if (icon_code) {
				text = wc_add_to_cart_params.i18n_view_cart + " " + icon_code;
			} else {
				text = wc_add_to_cart_params.i18n_view_cart;
			}
		} else {
			if (icon_code) {
				text = icon_code + " " + wc_add_to_cart_params.i18n_view_cart;
			} else {
				text = wc_add_to_cart_params.i18n_view_cart;
			}
		}

		$(".added_to_cart.wc-forward", p).html(text);
	});

	$(document.body).on("hover", ".item--wc_cart", function() {
		$(this).removeClass("cart-active");
	});

	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
		var expires = "expires=" + d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}

	// Switch View mod
	$(document.body).on("click", ".wc-view-switcher .wc-view-mod", function(e) {
		e.preventDefault();
		var mod = $(this).data("mod") || "grid";
		$(".wc-view-switcher .wc-view-mod").removeClass("active");
		$(this).addClass("active");
		$(".woocommerce-listing, .products").removeClass(
			"wc-grid-view wc-list-view"
		);
		$(".woocommerce-listing, .products").addClass("wc-" + mod + "-view");
		setCookie("customify_wc_pl_view_mod", mod, 360);
	});

	// Custom plus minus for product/cart quantity
	$.fn._wc_plus_minus = function() {
		this.each(function() {
			var input = $(this);
			var check = input.data("qty-added") || false;
			if (!check) {
				input.data("qty-added", 1);

				input.wrap('<span class="input-qty-pm"></span>');
				var p = input.parent();
				input.attr("type", "text"); // remove up/ down arrow default
				p.append(
					'<button type="button" class="input-pm-act input-pm-plus">+</button>'
				);
				p.prepend(
					'<button type="button" class="input-pm-act input-pm-minus">-</button>'
				);

				// This button will increment the value
				$(".input-pm-plus", p).click(function(e) {
					// Stop acting like a button
					e.preventDefault();
					// Get the field name
					// Get its current value
					var currentVal = parseInt(input.val());
					// If is not undefined
					if (!isNaN(currentVal)) {
						// Increment
						input.val(currentVal + 1);
					} else {
						// Otherwise put a 0 there
						input.val(0);
					}
					input.trigger("change");
				});
				// This button will decrement the value till 0
				$(".input-pm-minus", p).click(function(e) {
					// Stop acting like a button
					e.preventDefault();
					// Get the field name
					// Get its current value
					var currentVal = parseInt(input.val());
					// If it isn't undefined or its greater than 0
					if (!isNaN(currentVal) && currentVal > 0) {
						// Decrement one
						input.val(currentVal - 1);
					} else {
						// Otherwise put a 0 there
						input.val(0);
					}

					input.trigger("change");
				});
			}
		});
		return this;
	};

	if (woocommerce_params.qty_pm) {
		$('input.qty[type="number"]')._wc_plus_minus();
	}
	$(document.body).on("updated_wc_div", function() {
		$('input.qty[type="number"]')._wc_plus_minus();
	});

	// Product tabs
	function _slideDeneCallback() {
		$(document.body).trigger("wc_toggle_done");
	}
	var tabs = $(".wc-tabs-toggle");
	$(".tab-section", tabs)
		.not(":eq(0)")
		.removeClass("active")
		.find(".tab-section-content")
		.slideUp(_slideDeneCallback);
	tabs.on("click", ".tab-section-heading", function(e) {
		e.preventDefault();
		var section = $(this).closest(".tab-section");
		if (!section.hasClass("active")) {
			$(".tab-section", tabs)
				.removeClass("active")
				.find(".tab-section-content")
				.slideUp(_slideDeneCallback);
			section
				.toggleClass("active")
				.find(".tab-section-content")
				.slideDown(_slideDeneCallback);
		} else {
			$(".tab-section", tabs)
				.removeClass("active")
				.find(".tab-section-content")
				.slideUp(_slideDeneCallback);
		}
	});
});
