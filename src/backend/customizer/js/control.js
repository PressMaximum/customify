// React modules bundled into this control bundle.
import { observeAndMount as observeAndMountColumnsSettings } from './controls/columns-settings';
// Typography control split out into its own file for readability;
// still bundled into this entry, still called inside IIFE 2 below.
import { setupTypographyControl } from './typography-control';
import { attachPopoverChrome } from './popover-chrome';

(function (api) {
	// Extends our custom "example-1" section.
	api.sectionConstructor["customify-pro"] = api.Section.extend({
		// No events for this type of section.
		attachEvents: function () { },

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	});
})(wp.customize);

(function ($) {
	var api = wp.customize;

	api.bind("pane-contents-reflowed", function () {
		// Reflow sections
		var sections = [];
		api.section.each(function (section) {
			if (
				"customify_section" !== section.params.type ||
				"undefined" === typeof section.params.section
			) {
				return;
			}

			sections.push(section);
		});

		sections.sort(api.utils.prioritySort).reverse();
		$.each(sections, function (i, section) {
			var parentContainer = $(
				"#sub-accordion-section-" + section.params.section
			);
			parentContainer
				.children(".section-meta")
				.after(section.headContainer);
		});

		// Reflow panels
		var panels = [];
		api.panel.each(function (panel) {
			if (
				"customify_panel" !== panel.params.type ||
				"undefined" === typeof panel.params.panel
			) {
				return;
			}

			panels.push(panel);
		});
		panels.sort(api.utils.prioritySort).reverse();
		$.each(panels, function (i, panel) {
			var parentContainer = $(
				"#sub-accordion-panel-" + panel.params.panel
			);
			parentContainer.children(".panel-meta").after(panel.headContainer);
		});
	});

	// Extend Panel
	var _panelEmbed = wp.customize.Panel.prototype.embed;
	var _panelIsContextuallyActive =
		wp.customize.Panel.prototype.isContextuallyActive;
	var _panelAttachEvents = wp.customize.Panel.prototype.attachEvents;

	wp.customize.Panel = wp.customize.Panel.extend({
		attachEvents: function () {
			if (
				"customify_panel" !== this.params.type ||
				"undefined" === typeof this.params.panel
			) {
				_panelAttachEvents.call(this);
				return;
			}

			_panelAttachEvents.call(this);
			var panel = this;
			panel.expanded.bind(function (expanded) {
				var parent = api.panel(panel.params.panel);
				if (expanded) {
					parent.contentContainer.addClass("current-panel-parent");
				} else {
					parent.contentContainer.removeClass("current-panel-parent");
				}
			});

			panel.container
				.find(".customize-panel-back")
				.off("click keydown")
				.on("click keydown", function (event) {
					if (api.utils.isKeydownButNotEnterEvent(event)) {
						return;
					}
					event.preventDefault(); // Keep this AFTER the key filter above
					if (panel.expanded()) {
						api.panel(panel.params.panel).expand();
					}
				});
		},
		embed: function () {
			if (
				"customify_panel" !== this.params.type ||
				"undefined" === typeof this.params.panel
			) {
				_panelEmbed.call(this);
				return;
			}
			_panelEmbed.call(this);
			var panel = this;
			var parentContainer = $(
				"#sub-accordion-panel-" + this.params.panel
			);
			parentContainer.append(panel.headContainer);
		},
		isContextuallyActive: function () {
			if ("customify_panel" !== this.params.type) {
				return _panelIsContextuallyActive.call(this);
			}

			var panel = this;
			var children = this._children("panel", "section");
			api.panel.each(function (child) {
				if (!child.params.panel) {
					return;
				}
				if (child.params.panel !== panel.id) {
					return;
				}
				children.push(child);
			});

			children.sort(api.utils.prioritySort);
			var activeCount = 0;
			_(children).each(function (child) {
				if (child.active() && child.isContextuallyActive()) {
					activeCount += 1;
				}
			});
			return activeCount !== 0;
		}
	});

	// Extend Section
	var _sectionEmbed = wp.customize.Section.prototype.embed;
	var _sectionIsContextuallyActive =
		wp.customize.Section.prototype.isContextuallyActive;
	var _sectionAttachEvents = wp.customize.Section.prototype.attachEvents;

	wp.customize.Section = wp.customize.Section.extend({
		attachEvents: function () {
			if (
				"customify_section" !== this.params.type ||
				"undefined" === typeof this.params.section
			) {
				_sectionAttachEvents.call(this);
				return;
			}
			_sectionAttachEvents.call(this);
			var section = this;
			section.expanded.bind(function (expanded) {
				var parent = api.section(section.params.section);
				if (expanded) {
					parent.contentContainer.addClass("current-section-parent");
				} else {
					parent.contentContainer.removeClass(
						"current-section-parent"
					);
				}
			});

			section.container
				.find(".customize-section-back")
				.off("click keydown")
				.on("click keydown", function (event) {
					if (api.utils.isKeydownButNotEnterEvent(event)) {
						return;
					}
					event.preventDefault(); // Keep this AFTER the key filter above
					if (section.expanded()) {
						api.section(section.params.section).expand();
					}
				});
		},
		embed: function () {
			if (
				"customify_section" !== this.params.type ||
				"undefined" === typeof this.params.section
			) {
				_sectionEmbed.call(this);
				return;
			}

			_sectionEmbed.call(this);
			var section = this;
			var parentContainer = $(
				"#sub-accordion-section-" + this.params.section
			);
			parentContainer.append(section.headContainer);
		},
		isContextuallyActive: function () {
			if ("customify_section" !== this.params.type) {
				return _sectionIsContextuallyActive.call(this);
			}

			var section = this;
			var children = this._children("section", "control");
			api.section.each(function (child) {
				if (!child.params.section) {
					return;
				}

				if (child.params.section !== section.id) {
					return;
				}
				children.push(child);
			});

			children.sort(api.utils.prioritySort);
			var activeCount = 0;
			_(children).each(function (child) {
				if ("undefined" !== typeof child.isContextuallyActive) {
					if (child.active() && child.isContextuallyActive()) {
						activeCount += 1;
					}
				} else {
					if (child.active()) {
						activeCount += 1;
					}
				}
			});
			return activeCount !== 0;
		}
	});
})(jQuery);

(function ($, wpcustomize) {
	"use strict";

	var $document = $(document);
	var is_rtl = Customify_Control_Args.is_rtl;

	var CustomifyMedia = {
		setAttachment: function (attachment) {
			this.attachment = attachment;
		},
		addParamsURL: function (url, data) {
			if (!$.isEmptyObject(data)) {
				url += (url.indexOf("?") >= 0 ? "&" : "?") + $.param(data);
			}
			return url;
		},
		getThumb: function (attachment) {
			var control = this;
			if (typeof attachment !== "undefined") {
				this.attachment = attachment;
			}
			var t = new Date().getTime();
			if (typeof this.attachment.sizes !== "undefined") {
				if (typeof this.attachment.sizes.medium !== "undefined") {
					return control.addParamsURL(
						this.attachment.sizes.medium.url,
						{ t: t }
					);
				}
			}
			return control.addParamsURL(this.attachment.url, { t: t });
		},
		getURL: function (attachment) {
			if (typeof attachment !== "undefined") {
				this.attachment = attachment;
			}
			var t = new Date().getTime();
			return this.addParamsURL(this.attachment.url, { t: t });
		},
		getID: function (attachment) {
			if (typeof attachment !== "undefined") {
				this.attachment = attachment;
			}
			return this.attachment.id;
		},
		getInputID: function (attachment) {
			$(".attachment-id", this.preview).val();
		},
		setPreview: function ($el) {
			this.preview = $el;
		},
		insertImage: function (attachment) {
			if (typeof attachment !== "undefined") {
				this.attachment = attachment;
			}

			var url = this.getURL();
			var id = this.getID();
			var mime = this.attachment.mime;
			$(".customify-image-preview", this.preview)
				.addClass("customify--has-file")
				.html('<img src="' + url + '" alt="">');
			$(".attachment-url", this.preview).val(this.toRelativeUrl(url));
			$(".attachment-mime", this.preview).val(mime);
			$(".attachment-id", this.preview)
				.val(id)
				.trigger("change");
			this.preview.addClass("attachment-added");
			this.showChangeBtn();
		},
		toRelativeUrl: function (url) {
			return url;
			//return url.replace( Customify_Control_Args.home_url, '' );
		},
		showChangeBtn: function () {
			$(".customify--add", this.preview).addClass("customify--hide");
			$(".customify--change", this.preview).removeClass(
				"customify--hide"
			);
			$(".customify--remove", this.preview).removeClass(
				"customify--hide"
			);
		},
		insertVideo: function (attachment) {
			if (typeof attachment !== "undefined") {
				this.attachment = attachment;
			}

			var url = this.getURL();
			var id = this.getID();
			var mime = this.attachment.mime;
			var html =
				'<video width="100%" height="" controls><source src="' +
				url +
				'" type="' +
				mime +
				'">Your browser does not support the video tag.</video>';
			$(".customify-image-preview", this.preview)
				.addClass("customify--has-file")
				.html(html);
			$(".attachment-url", this.preview).val(this.toRelativeUrl(url));
			$(".attachment-mime", this.preview).val(mime);
			$(".attachment-id", this.preview)
				.val(id)
				.trigger("change");
			this.preview.addClass("attachment-added");
			this.showChangeBtn();
		},
		insertFile: function (attachment) {
			if (typeof attachment !== "undefined") {
				this.attachment = attachment;
			}
			var url = attachment.url;
			var mime = this.attachment.mime;
			var basename = url.replace(/^.*[\\\/]/, "");

			$(".customify-image-preview", this.preview)
				.addClass("customify--has-file")
				.html(
					'<a href="' +
					url +
					'" class="attachment-file" target="_blank">' +
					basename +
					"</a>"
				);
			$(".attachment-url", this.preview).val(this.toRelativeUrl(url));
			$(".attachment-mime", this.preview).val(mime);
			$(".attachment-id", this.preview)
				.val(this.getID())
				.trigger("change");
			this.preview.addClass("attachment-added");
			this.showChangeBtn();
		},
		remove: function ($el) {
			if (typeof $el !== "undefined") {
				this.preview = $el;
			}
			$(".customify-image-preview", this.preview)
				.removeAttr("style")
				.html("")
				.removeClass("customify--has-file");
			$(".attachment-url", this.preview).val("");
			$(".attachment-mime", this.preview).val("");
			$(".attachment-id", this.preview)
				.val("")
				.trigger("change");
			this.preview.removeClass("attachment-added");

			$(".customify--add", this.preview).removeClass("customify--hide");
			$(".customify--change", this.preview).addClass("customify--hide");
			$(".customify--remove", this.preview).addClass("customify--hide");
		}
	};

	CustomifyMedia.controlMediaImage = wp.media({
		title: wp.media.view.l10n.addMedia,
		multiple: false,
		library: { type: "image" }
	});

	CustomifyMedia.controlMediaImage.on("select", function () {
		var attachment = CustomifyMedia.controlMediaImage
			.state()
			.get("selection")
			.first()
			.toJSON();
		CustomifyMedia.insertImage(attachment);
	});

	CustomifyMedia.controlMediaVideo = wp.media({
		title: wp.media.view.l10n.addMedia,
		multiple: false,
		library: { type: "video" }
	});

	CustomifyMedia.controlMediaVideo.on("select", function () {
		var attachment = CustomifyMedia.controlMediaVideo
			.state()
			.get("selection")
			.first()
			.toJSON();
		CustomifyMedia.insertVideo(attachment);
	});

	CustomifyMedia.controlMediaFile = wp.media({
		title: wp.media.view.l10n.addMedia,
		multiple: false
	});

	CustomifyMedia.controlMediaFile.on("select", function () {
		var attachment = CustomifyMedia.controlMediaFile
			.state()
			.get("selection")
			.first()
			.toJSON();
		CustomifyMedia.insertFile(attachment);
	});

	var customify_controls_list = {};
	//---------------------------------------------------------------------------

	var customifyField = {
		devices: ["desktop", "tablet", "mobile"],
		allDevices: ["desktop", "tablet", "mobile"],
		type: "customify",
		getTemplate: _.memoize(function () {
			var field = this;
			var compiled,
				/*
				 * Underscore's default ERB-style templates are incompatible with PHP
				 * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
				 *
				 * @see trac ticket #22344.
				 */
				options = {
					evaluate: /<#([\s\S]+?)#>/g,
					interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
					escape: /\{\{([^\}]+?)\}\}(?!\})/g,
					variable: "data"
				};

			return function (data, id, data_variable_name) {
				if (_.isUndefined(id)) {
					//id = 'tmpl-customize-control-' + field.type;
					id = "tmpl-field-customify-" + field.type;
				}
				if (
					!_.isUndefined(data_variable_name) &&
					_.isString(data_variable_name)
				) {
					options.variable = data_variable_name;
				} else {
					options.variable = "data";
				}
				compiled = _.template($("#" + id).html(), null, options);
				return compiled(data);
			};
		}),

		getFieldValue: function (name, fieldSetting, $field) {
			var control = this;
			var type = undefined;
			var support_devices = false;

			if (!_.isUndefined(fieldSetting)) {
				type = fieldSetting.type;
				support_devices = fieldSetting.device_settings;
			}

			var value = "";
			switch (type) {
				case "media":
				case "image":
				case "video":
				case "attachment":
				case "audio":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							value[device] = {
								id: $(
									'input[data-name="' + _name + '"]',
									$field
								).val(),
								url: $(
									'input[data-name="' + _name + '-url"]',
									$field
								).val(),
								mime: $(
									'input[data-name="' + _name + '-mime"]',
									$field
								).val()
							};
						});
					} else {
						value = {
							id: $(
								'input[data-name="' + name + '"]',
								$field
							).val(),
							url: $(
								'input[data-name="' + name + '-url"]',
								$field
							).val(),
							mime: $(
								'input[data-name="' + name + '-mime"]',
								$field
							).val()
						};
					}

					break;
				case "css_ruler":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							value[device] = {
								unit: $(
									'input[data-name="' +
									_name +
									'-unit"]:checked',
									$field
								).val(),
								top: $(
									'input[data-name="' + _name + '-top"]',
									$field
								).val(),
								right: $(
									'input[data-name="' + _name + '-right"]',
									$field
								).val(),
								bottom: $(
									'input[data-name="' + _name + '-bottom"]',
									$field
								).val(),
								left: $(
									'input[data-name="' + _name + '-left"]',
									$field
								).val(),
								link: $(
									'input[data-name="' + _name + '-link"]',
									$field
								).is(":checked")
									? 1
									: ""
							};
						});
					} else {
						value = {
							unit: $(
								'input[data-name="' + name + '-unit"]:checked',
								$field
							).val(),
							top: $(
								'input[data-name="' + name + '-top"]',
								$field
							).val(),
							right: $(
								'input[data-name="' + name + '-right"]',
								$field
							).val(),
							bottom: $(
								'input[data-name="' + name + '-bottom"]',
								$field
							).val(),
							left: $(
								'input[data-name="' + name + '-left"]',
								$field
							).val(),
							link: $(
								'input[data-name="' + name + '-link"]',
								$field
							).is(":checked")
								? 1
								: ""
						};
					}

					break;
				case "shadow":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							value[device] = {
								color: $(
									'input[data-name="' + _name + '-color"]',
									$field
								).val(),
								x: $(
									'input[data-name="' + _name + '-x"]',
									$field
								).val(),
								y: $(
									'input[data-name="' + _name + '-y"]',
									$field
								).val(),
								blur: $(
									'input[data-name="' + _name + '-blur"]',
									$field
								).val(),
								spread: $(
									'input[data-name="' + _name + '-spread"]',
									$field
								).val(),
								inset: $(
									'input[data-name="' + _name + '-inset"]',
									$field
								).is(":checked")
									? 1
									: false
							};
						});
					} else {
						value = {
							color: $(
								'input[data-name="' + name + '-color"]',
								$field
							).val(),
							x: $(
								'input[data-name="' + name + '-x"]',
								$field
							).val(),
							y: $(
								'input[data-name="' + name + '-y"]',
								$field
							).val(),
							blur: $(
								'input[data-name="' + name + '-blur"]',
								$field
							).val(),
							spread: $(
								'input[data-name="' + name + '-spread"]',
								$field
							).val(),
							inset: $(
								'input[data-name="' + name + '-inset"]',
								$field
							).is(":checked")
								? 1
								: false
						};
					}

					break;
				case "font_style":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							value[device] = {
								b: $(
									'input[data-name="' + _name + '-b"]',
									$field
								).is(":checked")
									? 1
									: "",
								i: $(
									'input[data-name="' + _name + '-i"]',
									$field
								).is(":checked")
									? 1
									: "",
								u: $(
									'input[data-name="' + _name + '-u"]',
									$field
								).is(":checked")
									? 1
									: "",
								s: $(
									'input[data-name="' + _name + '-s"]',
									$field
								).is(":checked")
									? 1
									: "",
								t: $(
									'input[data-name="' + _name + '-t"]',
									$field
								).is(":checked")
									? 1
									: ""
							};
						});
					} else {
						value = {
							b: $(
								'input[data-name="' + name + '-b"]',
								$field
							).is(":checked")
								? 1
								: "",
							i: $(
								'input[data-name="' + name + '-i"]',
								$field
							).is(":checked")
								? 1
								: "",
							u: $(
								'input[data-name="' + name + '-u"]',
								$field
							).is(":checked")
								? 1
								: "",
							s: $(
								'input[data-name="' + name + '-s"]',
								$field
							).is(":checked")
								? 1
								: "",
							t: $(
								'input[data-name="' + name + '-t"]',
								$field
							).is(":checked")
								? 1
								: ""
						};
					}

					break;
				case "font":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							var subsets = {};
							$(
								'.list-subsets[data-name="' +
								_name +
								'-subsets"] input',
								$field
							).each(function () {
								if ($(this).is(":checked")) {
									var _v = $(this).val();
									subsets[_v] = _v;
								}
							});
							value[device] = {
								font: $(
									'select[data-name="' + _name + '-font"]',
									$field
								).val(),
								type: $(
									'input[data-name="' + _name + '-type"]',
									$field
								).val(),
								variant: $(
									'select[data-name="' + _name + '-variant"]',
									$field
								).val(), // variant
								subsets: subsets
							};
						});
					} else {
						var subsets = {};
						$(
							'.list-subsets[data-name="' +
							name +
							'-subsets"] input',
							$field
						).each(function () {
							if ($(this).is(":checked")) {
								var _v = $(this).val();
								subsets[_v] = _v;
							}
						});
						value = {
							font: $(
								'select[data-name="' + name + '-font"]',
								$field
							).val(),
							type: $(
								'input[data-name="' + name + '-type"]',
								$field
							).val(),
							variant: $(
								'select[data-name="' + name + '-variant"]',
								$field
							).val(),
							subsets: subsets
						};
					}

					break;
				case "slider":
					// Multi-unit sliders render the unit as a <select>;
					// legacy sliders keep the hidden checked radio. Read
					// whichever exists so saved units round-trip exactly.
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							value[device] = {
								unit: $(
									'select[data-name="' +
									_name +
									'-unit"], input[data-name="' +
									_name +
									'-unit"]:checked',
									$field
								).val(),
								value: $(
									'input[data-name="' + _name + '-value"]',
									$field
								).val()
							};
						});
					} else {
						value = {
							unit: $(
								'select[data-name="' +
								name +
								'-unit"], input[data-name="' +
								name +
								'-unit"]:checked',
								$field
							).val(),
							value: $(
								'input[data-name="' + name + '-value"]',
								$field
							).val()
						};
					}

					break;
				case "icon":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var _name = name + "-" + device;
							value[device] = {
								type: $(
									'input[data-name="' + _name + '-type"]',
									$field
								).val(),
								icon: $(
									'input[data-name="' + _name + '"]',
									$field
								).val()
							};
						});
					} else {
						value = {
							type: $(
								'input[data-name="' + name + '-type"]',
								$field
							).val(),
							icon: $(
								'input[data-name="' + name + '"]',
								$field
							).val()
						};
					}
					break;
				case "radio":
				case "text_align":
				case "text_align_no_justify":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							var input = $(
								'input[data-name="' +
								name +
								"-" +
								device +
								'"]:checked',
								$field
							);
							value[device] = input.length ? input.val() : "";
						});
					} else {
						value = $(
							'input[data-name="' + name + '"]:checked',
							$field
						).val();
					}

					break;
				case "checkbox":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							value[device] = $(
								'input[data-name="' +
								name +
								"-" +
								device +
								'"]',
								$field
							).is(":checked")
								? 1
								: "";
						});
					} else {
						value = $('input[data-name="' + name + '"]', $field).is(
							":checked"
						)
							? 1
							: "";
					}

					break;

				case "checkboxes":
					value = {};
					if (support_devices) {
						_.each(control.allDevices, function (device) {
							value[device] = {};
							$(
								'input[data-name="' +
								name +
								"-" +
								device +
								'"]',
								$field
							).each(function () {
								var v = $(this).val();
								if ($(this).is(":checked")) {
									value[v] = v;
								}
							});
						});
					} else {
						$('input[data-name="' + name + '"]', $field).each(
							function () {
								var v = $(this).val();
								if ($(this).is(":checked")) {
									value[v] = v;
								}
							}
						);
					}

					break;
				case "typography":
				case "modal":
				case "styling":
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							value[device] = $(
								'[data-name="' + name + "-" + device + '"]',
								$field
							).val();
						});
					} else {
						value = $('[data-name="' + name + '"]', $field).val();
					}

					try {
						value = JSON.parse(value);
					} catch (e) { }
					break;
				default:
					if (support_devices) {
						value = {};
						_.each(control.allDevices, function (device) {
							value[device] = $(
								'[data-name="' + name + "-" + device + '"]',
								$field
							).val();
						});
					} else {
						value = $('[data-name="' + name + '"]', $field).val();
					}
					break;
			}

			return value;
		},
		getValue: function (field, container) {
			var control = this;
			var value = "";

			switch (field.type) {
				case "group":
					value = {};

					if (field.device_settings) {
						_.each(control.allDevices, function (device) {
							var $area = $(
								".customify-group-device-fields.customify--for-" +
								device,
								container
							);
							value[device] = {};
							var _value = {};
							_.each(field.fields, function (f) {
								var $_field = $(
									'.customify--group-field[data-field-name="' +
									f.name +
									'"]',
									$area
								);
								_value[f.name] = control.getFieldValue(
									f.name,
									f,
									$_field
								);
							});
							value[device] = _value;
							control.initConditional($area, _value);
						});
					} else {
						_.each(field.fields, function (f) {
							var $_field = $(
								'.customify--group-field[data-field-name="' +
								f.name +
								'"]',
								container
							);
							value[f.name] = control.getFieldValue(
								f.name,
								f,
								$_field
							);
						});
						control.initConditional(container, value);
					}

					break;
				case "repeater":
					value = [];
					$(".customify--repeater-item", container).each(function (
						index
					) {
						var $item = $(this);
						var _v = {};
						_.each(field.fields, function (f) {
							var inputField = $(
								'[data-field-name="' + f.name + '"]',
								$item
							);
							//var $_field = inputField.closest('.customify--field');
							//var $_field = inputField.closest('.customify--repeater-field');
							var _fv = control.getFieldValue(f.name, f, $item);
							_v[f.name] = _fv;
							// Update Live title
							if (field.live_title_field == f.name) {
								if (inputField.prop("tagName") == "select") {
									_fv = $('option[value="' + _fv + '"]')
										.first()
										.text();
								} else if (_.isUndefined(_fv) || _fv == "") {
									//_fv = control.params.l10n.untitled;
									_fv = Customify_Control_Args.untitled;
								}
								control.updateRepeaterLiveTitle(_fv, $item, f);
							}
						});

						control.initConditional($item, _v);

						value[index] = _v;
						value[index]["_visibility"] = "visible";

						if ($("input.r-visible-input", $item).length) {
							if (
								!$("input.r-visible-input", $item).is(
									":checked"
								)
							) {
								value[index]["_visibility"] = "hidden";
							}
						}
					});
					break;
				default:
					value = this.getFieldValue(field.name, field, container);
					break;
			}

			return value;
		},
		encodeValue: function (value) {
			return encodeURI(JSON.stringify(value));
		},
		decodeValue: function (value) {
			return JSON.parse(decodeURI(value));
		},
		updateRepeaterLiveTitle: function (value, $item, field) {
			$(".customify--repeater-live-title", $item).text(value);
		},
		compare: function (value1, cond, value2) {
			var equal = false;
			switch (cond) {
				case "===":
					equal = value1 === value2 ? true : false;
					break;
				case ">":
					equal = value1 > value2 ? true : false;
					break;
				case "<":
					equal = value1 < value2 ? true : false;
					break;
				case "!=":
					equal = value1 != value2 ? true : false;
					break;
				case "empty":
					var _v = _.clone(value1);
					if (_.isObject(_v) || _.isArray(_v)) {
						_.each(_v, function (v, i) {
							if (_.isEmpty(v)) {
								delete _v[i];
							}
						});

						equal = _.isEmpty(_v) ? true : false;
					} else {
						equal = _.isNull(_v) || _v == "" ? true : false;
					}
					break;
				case "not_empty":
					var _v = _.clone(value1);
					if (_.isObject(_v) || _.isArray(_v)) {
						_.each(_v, function (v, i) {
							if (_.isEmpty(v)) {
								delete _v[i];
							}
						});
					}
					equal = _.isEmpty(_v) ? false : true;
					break;
				default:
					if (_.isArray(value2)) {
						if (!_.isEmpty(value2) && !_.isEmpty(value1)) {
							equal = _.contains(value2, value1);
						} else {
							equal = false;
						}
					} else {
						equal = value1 == value2 ? true : false;
					}
			}

			return equal;
		},
		multiple_compare: function (list, values, decodeValue) {
			if (_.isUndefined(decodeValue)) {
				decodeValue = false;
			}
			var control = this;
			var check = false;
			try {
				var test = list[0];

				if (_.isString(test)) {
					check = false;
					var cond = list[1];
					var cond_val = list[2];
					var cond_device = false;
					if (!_.isUndefined(list[3])) {
						// can be desktop, tablet, mobile
						cond_device = list[3];
					}
					var value;
					if (!_.isUndefined(values[test])) {
						value = values[test];
						if (cond_device) {
							if (
								_.isObject(value) &&
								!_.isUndefined(value[cond_device])
							) {
								value = value[cond_device];
							}
						}
						try {
							if (decodeValue) {
								value = control.decodeValue(value);
							}
						} catch (e) { }

						check = control.compare(value, cond, cond_val);
					}
				} else if (_.isArray(test)) {
					check = true;
					//console.log( '___', list );
					_.each(list, function (req) {
						var cond_key = req[0];
						var cond_cond = req[1];
						var cond_val = req[2];
						var cond_device = false;
						if (!_.isUndefined(req[3])) {
							// can be desktop, tablet, mobile
							cond_device = req[3];
						}
						var t_val = values[cond_key];
						if (_.isUndefined(t_val)) {
							t_val = "";
						}
						// console.log( '___reql', req );
						if (decodeValue && _.isString(t_val)) {
							try {
								t_val = control.decodeValue(t_val);
							} catch (e) { }
						}

						//console.log( '___t_val', t_val );
						if (cond_device) {
							if (
								_.isObject(t_val) &&
								!_.isUndefined(t_val[cond_device])
							) {
								t_val = t_val[cond_device];
							}
						}

						if (!control.compare(t_val, cond_cond, cond_val)) {
							check = false;
						}
					});
				}
			} catch (e) {
				//console.log( 'Trying_test_error', e  );
			}

			return check;
		},
		initConditional: function ($el, values) {
			var control = this;
			var $fields = $(".customify--field", $el);
			$fields.each(function () {
				var $field = $(this);
				var check = true;
				var req = $field.attr("data-required") || false;
				if (!_.isUndefined(req) && req) {
					req = JSON.parse(req);
					check = control.multiple_compare(req, values);
					if (!check) {
						$field.addClass("customify--hide");
					} else {
						$field.removeClass("customify--hide");
					}
				}
			});
		},

		addDeviceSwitchers: function ($el) {
			var field = this;
			if (_.isUndefined($el)) {
				$el = field.container;
			}
			var clone = $("#customize-footer-actions .devices").clone();
			clone.addClass("customify-devices");
			$("button", clone).each(function () {
				var d = $(this).attr("data-device");
				if (_.indexOf(field.devices, d) < 0) {
					$(this).remove();
				}
			});
			$(".customify-field-heading", $el)
				.append(clone)
				.addClass("customify-devices-added");
		},

		addRepeaterItem: function (field, value, $container, cb) {
			if (!_.isObject(value)) {
				value = {};
			}

			var control = this;
			var template = control.getTemplate();
			var fields = field.fields;
			var addable = true;
			var title_only = field.title_only;
			if (field.addable === false) {
				addable = false;
			}

			var $itemWrapper = $(
				template(field, "tmpl-customize-control-repeater-layout")
			);
			$container.find(".customify--settings-fields").append($itemWrapper);
			_.each(fields, function (f, index) {
				f.value = "";
				f.addable = addable;
				if (!_.isUndefined(value[f.name])) {
					f.value = value[f.name];
				}
				var $fieldArea;
				$fieldArea = $('<div class="customify--repeater-field"></div>');
				$(".customify--repeater-item-inner", $itemWrapper).append(
					$fieldArea
				);
				control.add(f, $fieldArea, function () {
					if (_.isFunction(cb)) {
						cb();
					}
				});

				var live_title = f.value;
				// Update Live title
				if (field.live_title_field === f.name) {
					if (f.type === "select") {
						live_title = f.choices[f.value];
					} else if (_.isUndefined(live_title) || live_title == "") {
						live_title = Customify_Control_Args.untitled;
					}
					control.updateRepeaterLiveTitle(
						live_title,
						$itemWrapper,
						f
					);
				}
			});

			if (
				!_.isUndefined(value._visibility) &&
				value._visibility === "hidden"
			) {
				$itemWrapper.addClass("item---visible-hidden");
				$itemWrapper
					.find("input.r-visible-input")
					.removeAttr("checked");
			} else {
				$itemWrapper
					.find("input.r-visible-input")
					.attr("checked", "checked");
			}

			if (title_only) {
				$(
					".customify--repeater-item-settings, .customify--repeater-item-toggle",
					$itemWrapper
				).hide();
			}

			$document.trigger("customify/customizer/repeater/add", [
				$itemWrapper,
				control
			]);
			return $itemWrapper;
		},
		limitRepeaterItems: function (field, $container) {
			return;
			var control = this;
			var addButton = $(".customify--repeater-add-new", $container);
			var c = $(
				".customify--settings-fields .customify--repeater-item",
				$container
			).length;

			if (control.params.limit > 0) {
				if (c >= control.params.limit) {
					addButton.addClass("customify--hide");
					if (control.params.limit_msg) {
						if (
							$(".customify--limit-item-msg", control.container)
								.length === 0
						) {
							$(
								'<p class="customify--limit-item-msg">' +
								control.params.limit_msg +
								"</p>"
							).insertBefore(addButton);
						} else {
							$(
								".customify--limit-item-msg",
								control.container
							).removeClass("customify--hide");
						}
					}
				} else {
					$(".customify--limit-item-msg", control.container).addClass(
						"customify--hide"
					);
					addButton.removeClass("customify--hide");
				}
			}

			if (c > 0) {
				$(
					".customify--repeater-reorder",
					control.container
				).removeClass("customify--hide");
			} else {
				$(".customify--repeater-reorder", control.container).addClass(
					"customify--hide"
				);
			}
		},
		initRepeater: function (field, $container, cb) {
			var control = this;
			field = _.defaults(field, {
				addable: null,
				title_only: null,
				limit: null,
				live_title_field: null,
				fields: null
			});
			field.limit = parseInt(field.limit);
			if (isNaN(field.limit)) {
				field.limit = 0;
			}

			// Sortable
			$container.find(".customify--settings-fields").sortable({
				handle: ".customify--repeater-item-heading",
				containment: "parent",
				update: function (event, ui) {
					// control.getValue();
					if (_.isFunction(cb)) {
						cb();
					}
				}
			});

			// Toggle Move
			$container.on("click", ".customify--repeater-reorder", function (e) {
				e.preventDefault();
				$(".customify--repeater-items", $container).toggleClass(
					"reorder-active"
				);
				$(".customify--repeater-add-new", $container).toggleClass(
					"disabled"
				);
				if (
					$(".customify--repeater-items", $container).hasClass(
						"reorder-active"
					)
				) {
					$(this).html($(this).data("done"));
				} else {
					$(this).html($(this).data("text"));
				}
			});

			// Move Up
			$container.on(
				"click",
				".customify--repeater-item .customify--up",
				function (e) {
					e.preventDefault();
					var i = $(this).closest(".customify--repeater-item");
					var index = i.index();
					if (index > 0) {
						var up = i.prev();
						i.insertBefore(up);
						if (_.isFunction(cb)) {
							cb();
						}
					}
				}
			);

			// Move Down
			$container.on(
				"click",
				".customify--repeater-item .customify--down",
				function (e) {
					e.preventDefault();
					var n = $(
						".customify--repeater-items .customify--repeater-item",
						$container
					).length;
					var i = $(this).closest(".customify--repeater-item");
					var index = i.index();
					if (index < n - 1) {
						var down = i.next();
						i.insertAfter(down);
						if (_.isFunction(cb)) {
							cb();
						}
					}
				}
			);

			// Add item when customizer loaded
			if (_.isArray(field.value)) {
				_.each(field.value, function (itemValue) {
					control.addRepeaterItem(field, itemValue, $container, cb);
				});
				//control.getValue(false);
			}
			control.limitRepeaterItems();

			// Toggle visibility
			$container.on(
				"change",
				".customify--repeater-item .r-visible-input",
				function (e) {
					e.preventDefault();
					var p = $(this).closest(".customify--repeater-item");
					if ($(this).is(":checked")) {
						p.removeClass("item---visible-hidden");
					} else {
						p.addClass("item---visible-hidden");
					}
				}
			);

			// Toggle
			if (!field.title_only) {
				$container.on(
					"click",
					".customify--repeater-item-toggle, .customify--repeater-live-title",
					function (e) {
						e.preventDefault();
						var p = $(this).closest(".customify--repeater-item");
						p.toggleClass("customify--open");
					}
				);
			}

			// Remove
			$container.on("click", ".customify--remove", function (e) {
				e.preventDefault();
				var p = $(this).closest(".customify--repeater-item");
				p.remove();
				$document.trigger("customify/customizer/repeater/remove", [
					control
				]);
				if (_.isFunction(cb)) {
					cb();
				}
				control.limitRepeaterItems();
			});

			var defaultValue = {};
			_.each(field.fields, function (f, k) {
				defaultValue[f.name] = null;
				if (!_.isUndefined(f.default)) {
					defaultValue[f.name] = f.default;
				}
			});

			// Add Item
			$container.on("click", ".customify--repeater-add-new", function (e) {
				e.preventDefault();
				if (!$(this).hasClass("disabled")) {
					control.addRepeaterItem(
						field,
						defaultValue,
						$container,
						cb
					);
					if (_.isFunction(cb)) {
						cb();
					}
					control.limitRepeaterItems();
				}
			});
		},

		add: function (field, $fieldsArea, cb) {
			var control = this;
			var template = control.getTemplate();
			var template_id = "tmpl-field-" + control.type + "-" + field.type;
			if ($("#" + template_id).length == 0) {
				template_id = "tmpl-field-" + control.type + "-text";
			}

			if (field.device_settings) {
				var fieldItem = null;
				_.each(control.devices, function (device, index) {
					var _field = _.clone(field);
					_field.original_name = field.name;
					if (_.isObject(field.value)) {
						if (!_.isUndefined(field.value[device])) {
							_field.value = field.value[device];
						} else {
							_field.value = "";
						}
					} else {
						_field.value = "";
						if (index === 0) {
							_field.value = field.value;
						}
					}
					_field.name = field.name + "-" + device;
					_field._current_device = device;

					var $deviceFields = $(
						template(_field, template_id, "field")
					);
					var deviceFieldItem = $deviceFields
						.find(".customify-field-settings-inner")
						.first();

					if (!fieldItem) {
						$fieldsArea
							.append($deviceFields)
							.addClass("customify--multiple-devices");
					}

					deviceFieldItem.addClass("customify--for-" + device);
					deviceFieldItem.attr("data-for-device", device);

					if (fieldItem) {
						deviceFieldItem.insertAfter(fieldItem);
						fieldItem = deviceFieldItem;
					}
					fieldItem = deviceFieldItem;
				});
			} else {
				field.original_name = field.name;
				var $fields = template(field, template_id, "field");
				$fieldsArea.html($fields);
			}

			// Repeater
			if (field.type === "repeater") {
				var $rf_area = $(
					template(field, "tmpl-customize-control-repeater-inner")
				);
				$fieldsArea
					.find(".customify-field-settings-inner")
					.replaceWith($rf_area);
				control.initRepeater(field, $rf_area, cb);
			}

			if (field.css_format && _.isString(field.css_format)) {
				if (field.css_format.indexOf("value_no_unit") > 0) {
					$fieldsArea
						.find(".customify--slider-input")
						.addClass("no-unit");
					$(
						".customify--css-unit .customify--label-active",
						$fieldsArea
					).hide();
				}
			}

			// Add unility
			switch (field.type) {
				case "color":
				case "shadow":
					control.initColor($fieldsArea);
					break;
				case "image":
				case "video":
				case "audio":
				case "attchment":
				case "file":
					control.initMedia($fieldsArea);
					break;
				case "slider":
					control.initSlider($fieldsArea);
					break;
				case "css_ruler":
					control.initCSSRuler($fieldsArea, cb);
					break;
			}
			if (field.type !== "hidden") {
				if (
					!_.isUndefined(field.device_settings) &&
					field.device_settings
				) {
					control.addDeviceSwitchers($fieldsArea);
				}
			}
		},

		addFields: function (fields, values, $fieldsArea, cb) {
			var control = this;
			if (!_.isObject(values)) {
				values = {};
			}
			_.each(fields, function (f, index) {
				if (_.isUndefined(f.class)) {
					f.class = "";
				}
				var $fieldArea = $(
					'<div class="customify--group-field ft--' +
					f.type +
					" " +
					f.class +
					'" data-field-name="' +
					f.name +
					'"></div>'
				);
				$fieldsArea.append($fieldArea);
				f.original_name = f.name;
				if (!_.isUndefined(values[f.name])) {
					f.value = values[f.name];
				} else if (!_.isUndefined(f.default)) {
					f.value = f.default;
				} else {
					f.value = null;
				}
				control.add(f, $fieldArea, cb);
			});
		},

		initSlider: function ($el) {
			if ($(".customify-input-slider", $el).length > 0) {
				$(".customify-input-slider", $el).each(function () {
					var slider = $(this);
					var p = slider.parent();
					var input = $(".customify--slider-input", p);
					var min = slider.data("min") || 0;
					var max = slider.data("max") || 300;
					var step = slider.data("step") || 1;
					if (!_.isNumber(min)) {
						min = 0;
					}

					if (!_.isNumber(max)) {
						max = 300;
					}

					if (!_.isNumber(step)) {
						step = 1;
					}

					// Display-only handle seeding: with no saved value, park
					// the handle at the field's placeholder (the effective
					// CSS default) so the user starts dragging from the
					// documented starting point. Programmatic .slider("value")
					// does NOT fire `slide`, so the input stays empty and
					// nothing is saved until the user actually interacts.
					var seedHandle = function () {
						var ph = parseFloat(input.attr("placeholder"));
						if (!isNaN(ph)) {
							slider.slider("value", ph);
						}
					};

					var current_val = input.val();
					if ("" === current_val) {
						var _ph = parseFloat(input.attr("placeholder"));
						if (!isNaN(_ph)) {
							current_val = _ph;
						}
					}
					slider.slider({
						range: "min",
						value: current_val,
						step: step,
						min: min,
						max: max,
						slide: function (event, ui) {
							input.val(ui.value).trigger("data-change");
						}
					});

					input.on("change", function () {
						if ("" === $(this).val()) {
							// Cleared (or never set) — fall back to the
							// placeholder starting point instead of
							// dropping the handle to the range minimum.
							seedHandle();
							return;
						}
						slider.slider("value", $(this).val());
					});

					// Reset
					var wrapper = slider.closest(
						".customify-input-slider-wrapper"
					);
					wrapper.on("click", ".reset", function (e) {
						e.preventDefault();
						var d = slider.data("default");
						if (!_.isObject(d)) {
							d = {
								unit: "px",
								value: ""
							};
						}

						$(".customify--slider-input", wrapper).val(d.value);
						slider.slider("option", "value", d.value);
						var $unitSelect = $(
							".customify--unit-select",
							wrapper
						);
						if ($unitSelect.length) {
							var targetUnit = d.unit || "px";
							// No default VALUE → reset = pristine state:
							// re-derive the unit from the display default
							// (the same rule the template applies on first
							// render) so the seeded handle lands on e.g.
							// 2.1em — not 2.1 on the px scale, which reads
							// as zero.
							if (
								_.isUndefined(d.value) ||
								null === d.value ||
								"" === d.value
							) {
								var phm = String(
									input.attr("placeholder") || ""
								).match(/^-?[0-9.]+\s*([a-z%]+)?$/i);
								var pranges = slider.data("units");
								if (phm && _.isObject(pranges)) {
									var pu = phm[1]
										? phm[1].toLowerCase()
										: "-";
									if (pranges[pu]) {
										targetUnit = pu;
									}
								}
							}
							// Restore via the select — re-ranges through
							// its own change handler below.
							$unitSelect.val(targetUnit).trigger("change");
						} else {
							$(
								'.customify--css-unit input.customify-input[value="' +
								d.unit +
								'"]',
								wrapper
							).trigger("click");
						}
						$(".customify--slider-input", wrapper).trigger(
							"change"
						);
					});

					// Multi-unit slider: `data-units` maps unit =>
					// {min, max, step} (see `units` in get_typo_fields()).
					// Switching the unit re-ranges the slider + number
					// input and clamps the current value into the new
					// range. Saved shape stays {value, unit} — a unit is
					// only written when the user actively changes it. An
					// unknown saved unit (rendered as its own option for
					// lossless round-trip) has no range entry: keep the
					// current range untouched.
					var unitRanges = slider.data("units");
					var $unitSelect = $(".customify--unit-select", wrapper);
					if (_.isObject(unitRanges) && $unitSelect.length) {
						// Track the outgoing unit so a switch can CONVERT
						// the current number instead of reusing it raw —
						// 2.42em becomes ≈39px, not a near-zero 2.42px.
						$unitSelect.data("prevUnit", $unitSelect.val());
						// px-equivalence factors. em/rem/unitless treat
						// 1 ≈ 16px — an approximation, but it keeps the
						// value in the same visual ballpark on switch.
						// Conversion only runs on an explicit user unit
						// change with a non-empty value, through the
						// normal save path — never silently on load.
						var UNIT_PX_FACTOR = {
							px: 1,
							em: 16,
							rem: 16,
							"-": 16
						};
						$unitSelect.on("change", function () {
							var next = $(this).val();
							var prev = $(this).data("prevUnit");
							$(this).data("prevUnit", next);
							var r = unitRanges[next];
							if (!_.isObject(r)) {
								return;
							}
							var rMin = parseFloat(r.min);
							var rMax = parseFloat(r.max);
							var rStep = parseFloat(r.step);
							slider.slider("option", {
								min: rMin,
								max: rMax,
								step: rStep
							});
							input.attr({
								min: rMin,
								max: rMax,
								step: rStep
							});
							var v = input.val();
							if (v !== "") {
								v = parseFloat(v);
								if (
									prev !== next &&
									UNIT_PX_FACTOR[prev] &&
									UNIT_PX_FACTOR[next]
								) {
									v =
										(v * UNIT_PX_FACTOR[prev]) /
										UNIT_PX_FACTOR[next];
									v =
										"px" === next
											? Math.round(v)
											: Math.round(v * 100) / 100;
								}
								var clamped = Math.min(
									Math.max(v, rMin),
									rMax
								);
								input.val(clamped);
								slider.slider("value", clamped);
							}
							// Persist the new unit: the select itself is
							// `.change-by-js` (ignored by the standalone
							// control's save delegate); the number input
							// is not — its change reaches every context.
							input.trigger("change");
						});
					}
				});
			}
		},

		initMedia: function ($el) {
			// When add/Change
			$el.on(
				"click",
				".customify--media .customify--add, .customify--media .customify--change, .customify--media .customify-image-preview",
				function (e) {
					e.preventDefault();
					var p = $(this).closest(".customify--media");
					CustomifyMedia.setPreview(p);
					CustomifyMedia.controlMediaImage.open();
				}
			);

			// When add/Change
			$el.on("click", ".customify--media .customify--remove", function (
				e
			) {
				e.preventDefault();
				var p = $(this).closest(".customify--media");
				CustomifyMedia.remove(p);
			});
		},

		initCSSRuler: function ($el, change_cb) {
			// When toggle value change
			$el.on("change", ".customify--label-parent", function () {
				if ($(this).attr("type") == "radio") {
					var name = $(this).attr("name");
					$('input[name="' + name + '"]', $el)
						.parent()
						.removeClass("customify--label-active");
				}
				var checked = $(this).is(":checked");
				if (checked) {
					$(this)
						.parent()
						.addClass("customify--label-active");
				} else {
					$(this)
						.parent()
						.removeClass("customify--label-active");
				}
				if (_.isFunction(change_cb)) {
					change_cb();
				}
			});

			$el.on(
				"change keyup",
				".customify--css-ruler .customify-input-css",
				function () {
					var p = $(this).closest(".customify--css-ruler");
					var link_checked = $(
						".customify--css-ruler-link input",
						p
					).is(":checked");
					if (link_checked) {
						var v = $(this).val();
						$(".customify-input-css", p)
							.not($(this))
							.each(function () {
								if (!$(this).is(":disabled")) {
									$(this).val(v);
								}
							});
					}
					if (_.isFunction(change_cb)) {
						change_cb();
					}
				}
			);
		},

		initColor: function ($el) {
			$(".customify-input-color", $el).each(function () {
				var colorInput = $(this);
				var df = colorInput.data("default") || "";
				var current_val = $(
					".customify-input--color",
					colorInput
				).val();
				// data-alpha="true"
				$(".customify--color-panel", colorInput).attr(
					"data-alpha",
					"true"
				);
				$(".customify--color-panel", colorInput).wpColorPicker({
					defaultColor: df,
					change: function (event, ui) {
						var new_color = ui.color.toString();
						$(".customify-input--color", colorInput).val(new_color);
						if (ui.color.toString() !== current_val) {
							current_val = new_color;
							$(".customify-input--color", colorInput).trigger(
								"change"
							);
						}
					},
					clear: function (event, ui) {
						$(".customify-input--color", colorInput).val("");
						$(".customify-input--color", colorInput).trigger(
							"data-change"
						);
					}
				});
			});
		}
	};

	//-------------------------------------------------------------------------

	var customify_controlConstructor = {
		devices: ["desktop", "tablet", "mobile"],
		// When we're finished loading continue processing
		type: "customify",
		settingField: null,

		getTemplate: _.memoize(function () {
			var control = this;
			var compiled,
				/*
				 * Underscore's default ERB-style templates are incompatible with PHP
				 * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
				 *
				 * @see trac ticket #22344.
				 */
				options = {
					evaluate: /<#([\s\S]+?)#>/g,
					interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
					escape: /\{\{([^\}]+?)\}\}(?!\})/g,
					variable: "data"
				};

			return function (data, id, data_variable_name) {
				if (_.isUndefined(id)) {
					id = "tmpl-field-customify-" + control.type;
				}
				if (
					!_.isUndefined(data_variable_name) &&
					_.isString(data_variable_name)
				) {
					options.variable = data_variable_name;
				} else {
					options.variable = "data";
				}

				compiled = _.template($("#" + id).html(), null, options);
				return compiled(data);
			};
		}),
		addDeviceSwitchers: customifyField.addDeviceSwitchers,
		init: function () {
			var control = this;

			if (
				_.isArray(control.params.devices) &&
				!_.isEmpty(control.params.devices)
			) {
				control.devices = control.params.devices;
			}

			// The hidden field that keeps the data saved (though we never update it)
			control.settingField = control.container
				.find("[data-customize-setting-link]")
				.first();

			switch (control.params.setting_type) {
				case "group":
					control.initGroup();
					break;
				case "repeater":
					control.initRepeater();
					break;
				default:
					control.initField();
					break;
			}

			control.container.on(
				"change keyup data-change",
				"input:not(.change-by-js), select:not(.change-by-js), textarea:not(.change-by-js)",
				function () {
					control.getValue();
				}
			);

			// Sync the control DOM when the bound setting changes externally —
			// e.g. Multiple Headers no-reload variant switch fires
			// wp.customize(key).set(NEW_VALUE), and the control needs to
			// repaint its fields to reflect the new value. Without this bind
			// the control's DOM stays frozen on the value it rendered at mount
			// time even though the underlying setting has changed.
			//
			// Echo guard: getValue() → encodeValue() → setting.set() will fire
			// this same change handler. The `_customifyWriting` flag set
			// around setting.set() in getValue() lets us short-circuit those
			// self-triggered events.
			if (control.setting && typeof control.setting.bind === "function") {
				control.setting.bind(function () {
					if (control._customifyWriting) {
						return;
					}
					control.refreshFromSetting();
				});
			}
		},

		/**
		 * Repaint the control's form fields from the bound setting's current
		 * value. Called when an external actor (Multiple Headers variant
		 * switch, programmatic theme-mod write) mutates the setting without
		 * going through this control's UI.
		 *
		 * Strategy: clear the fields area, sync params.value from the setting,
		 * then re-run the type-appropriate init (initGroup / initRepeater /
		 * initField). The container-level "change keyup" delegate added in
		 * init() is left intact — emptying the inner DOM detaches its inputs
		 * but the delegation survives, so we do NOT re-bind it (rebinding
		 * would double-fire getValue on every keystroke).
		 */
		refreshFromSetting: function () {
			var control = this;
			if (typeof control.decodeValue !== "function") {
				return;
			}
			var raw = control.setting.get();
			var decoded;
			try {
				decoded = control.decodeValue(raw);
			} catch (e) {
				decoded = raw;
			}
			control.params.value = decoded;

			var $area = control.container.find(".customify--settings-fields");
			if (!$area.length) {
				return;
			}
			$area.empty();

			control._customifyRefreshing = true;
			try {
				switch (control.params.setting_type) {
					case "group":
						control.initGroup();
						break;
					case "repeater":
						control.initRepeater();
						break;
					default:
						control.initField();
						break;
				}
			} finally {
				control._customifyRefreshing = false;
			}

			// Chrome hooks (e.g. the typography trigger preview) re-render
			// from the freshly painted DOM on this event.
			control.container.trigger("customify/control/refreshed");
		},
		addParamsURL: function (url, data) {
			if (!$.isEmptyObject(data)) {
				url += (url.indexOf("?") >= 0 ? "&" : "?") + $.param(data);
			}
			return url;
		},

		compare: customifyField.compare,
		multiple_compare: customifyField.multiple_compare,
		initConditional: customifyField.initConditional,

		getValue: function (save) {
			var control = this;
			var value = "";

			var field = _.clone(control.params);

			field.type = control.params.setting_type;
			field.name = control.id;
			field.value = control.value;
			field.default = control.params.default;
			field.devices = control.params.devices;

			if (field.type === "slider") {
				field.min = control.params.min;
				field.max = control.params.max;
				field.step = control.params.step;
				field.unit = control.params.unit;
			}

			if (field.type === "css_ruler") {
				field.fields_disabled = control.params.fields_disabled;
			}

			if (field.type === "group" || field.type === "repeater") {
				field.fields = control.params.fields;
				field.live_title_field = control.params.live_title_field;
			}

			if (
				control.params.setting_type === "select" ||
				control.params.setting_type === "radio"
			) {
				field.choices = control.params.choices;
			}
			if (control.params.setting_type === "checkbox") {
				field.checkbox_label = control.params.checkbox_label;
			}

			field.device_settings = control.params.device_settings;

			value = customifyField.getValue(
				field,
				$(".customify--settings-fields", control.container)
			);

			if (_.isUndefined(save) || save) {
				// Flag the write so the setting.bind handler installed in
				// init() can distinguish self-triggered changes from external
				// ones and skip refreshFromSetting() — otherwise every
				// keystroke would empty/rebuild the field DOM mid-edit.
				control._customifyWriting = true;
				try {
					control.setting.set(control.encodeValue(value));
				} finally {
					control._customifyWriting = false;
				}

				// Need improve next version
				if (_.isArray(control.params.reset_controls)) {
					_.each(control.params.reset_controls, function (_cid) {
						try {
							var c = wpcustomize.control(_cid);
							c.setting.set(
								control.encodeValue(c.params.default)
							);
						} catch (e) { }
					});
				}

				$document.trigger("customify/customizer/value_changed", [
					control,
					value
				]);
			} else {
			}

			return value;
		},
		encodeValue: function (value) {
			return encodeURI(JSON.stringify(value));
		},
		decodeValue: function (value) {
			return JSON.parse(decodeURI(value));
		},
		updateRepeaterLiveTitle: function (value, $item, field) {
			$(".customify--repeater-live-title", $item).text(value);
		},
		initGroup: function () {
			var control = this;
			if (control.params.device_settings) {
				control.container
					.find(".customify--settings-fields")
					.addClass("customify--multiple-devices");
				if (!_.isObject(control.params.value)) {
					control.params.value = {};
				}

				_.each(control.devices, function (device, device_index) {
					var $group_device = $(
						'<div class="customify-group-device-fields customify-field-settings-inner customify--for-' +
						device +
						'"></div>'
					);
					control.container
						.find(".customify--settings-fields")
						.append($group_device);
					var device_value = {};
					if (!_.isUndefined(control.params.value[device])) {
						device_value = control.params.value[device];
					}
					if (!_.isObject(device_value)) {
						device_value = {};
					}

					customifyField.addFields(
						control.params.fields,
						device_value,
						$group_device,
						function () {
							control.getValue();
						}
					);
				});
			} else {
				customifyField.addFields(
					control.params.fields,
					control.params.value,
					control.container.find(".customify--settings-fields"),
					function () {
						control.getValue();
					}
				);
			}

			control.getValue(false);
		},
		addField: function (field, $fieldsArea, cb) {
			customifyField.devices = _.clone(this.devices);
			customifyField.add(field, $fieldsArea, cb);
		},
		initField: function () {
			var control = this;
			var field = _.clone(control.params);

			field = _.extend(field, {
				type: control.params.setting_type,
				name: control.id,
				value: control.params.value,
				default: control.params.default,
				devices: control.params.devices,
				unit: control.params.unit,
				title: null,
				label: null,
				description: null
			});

			if (field.type == "slider") {
				field.min = control.params.min;
				field.max = control.params.max;
				field.step = control.params.step;
			}

			if (field.type == "css_ruler") {
				field.fields_disabled = control.params.fields_disabled;
			}

			if (
				control.params.setting_type == "select" ||
				control.params.setting_type == "radio"
			) {
				field.choices = control.params.choices;
			}
			if (control.params.setting_type == "checkbox") {
				field.checkbox_label = control.params.checkbox_label;
			}

			field.device_settings = control.params.device_settings;
			var $fieldsArea = control.container.find(
				".customify--settings-fields"
			);

			control.addField(field, $fieldsArea, function () {
				control.getValue();
			});
			if (field.type !== "hidden") {
				if (
					!_.isUndefined(field.device_settings) &&
					field.device_settings
				) {
					control.addDeviceSwitchers(control.container);
				}
			}
		},
		addRepeaterItem: function (value) {
			if (!_.isObject(value)) {
				value = {};
			}

			var control = this;
			var template = control.getTemplate();
			var fields = control.params.fields;
			var addable = true;
			var title_only = control.params.title_only;
			if (control.params.addable === false) {
				addable = false;
			}

			var $itemWrapper = $(
				template(control.params, "tmpl-customize-control-repeater-item")
			);
			control.container
				.find(".customify--settings-fields")
				.append($itemWrapper);
			_.each(fields, function (f, index) {
				f.value = "";
				f.addable = addable;
				if (!_.isUndefined(value[f.name])) {
					f.value = value[f.name];
				}
				var $fieldArea;
				$fieldArea = $('<div class="customify--repeater-field"></div>');
				$(".customify--repeater-item-inner", $itemWrapper).append(
					$fieldArea
				);
				control.addField(f, $fieldArea, function () {
					control.getValue();
				});
			});

			if (
				!_.isUndefined(value._visibility) &&
				value._visibility === "hidden"
			) {
				$itemWrapper.addClass("item---visible-hidden");
				$itemWrapper
					.find("input.r-visible-input")
					.removeAttr("checked");
			} else {
				$itemWrapper
					.find("input.r-visible-input")
					.attr("checked", "checked");
			}

			if (title_only) {
				$(
					".customify--repeater-item-settings, .customify--repeater-item-toggle",
					$itemWrapper
				).hide();
			}

			$document.trigger("customify/customizer/repeater/add", [
				$itemWrapper,
				control
			]);
			return $itemWrapper;
		},
		limitRepeaterItems: function () {
			var control = this;

			var addButton = $(
				".customify--repeater-add-new",
				control.container
			);
			var c = $(
				".customify--settings-fields .customify--repeater-item",
				control.container
			).length;

			if (control.params.limit > 0) {
				if (c >= control.params.limit) {
					addButton.addClass("customify--hide");
					if (control.params.limit_msg) {
						if (
							$(".customify--limit-item-msg", control.container)
								.length === 0
						) {
							$(
								'<p class="customify--limit-item-msg">' +
								control.params.limit_msg +
								"</p>"
							).insertBefore(addButton);
						} else {
							$(
								".customify--limit-item-msg",
								control.container
							).removeClass("customify--hide");
						}
					}
				} else {
					$(".customify--limit-item-msg", control.container).addClass(
						"customify--hide"
					);
					addButton.removeClass("customify--hide");
				}
			}

			if (c > 0) {
				$(
					".customify--repeater-reorder",
					control.container
				).removeClass("customify--hide");
			} else {
				$(".customify--repeater-reorder", control.container).addClass(
					"customify--hide"
				);
			}
		},
		initRepeater: function () {
			var control = this;
			control.params.limit = parseInt(control.params.limit);
			if (isNaN(control.params.limit)) {
				control.params.limit = 0;
			}

			// Sortable
			control.container.find(".customify--settings-fields").sortable({
				handle: ".customify--repeater-item-heading",
				containment: "parent",
				update: function (event, ui) {
					control.getValue();
				}
			});

			// Toggle Move
			control.container.on(
				"click",
				".customify--repeater-reorder",
				function (e) {
					e.preventDefault();
					$(
						".customify--repeater-items",
						control.container
					).toggleClass("reorder-active");
					$(
						".customify--repeater-add-new",
						control.container
					).toggleClass("disabled");
					if (
						$(
							".customify--repeater-items",
							control.container
						).hasClass("reorder-active")
					) {
						$(this).html($(this).data("done"));
					} else {
						$(this).html($(this).data("text"));
					}
				}
			);

			// Move Up
			control.container.on(
				"click",
				".customify--repeater-item .customify--up",
				function (e) {
					e.preventDefault();
					var i = $(this).closest(".customify--repeater-item");
					var index = i.index();
					if (index > 0) {
						var up = i.prev();
						i.insertBefore(up);
						control.getValue();
					}
				}
			);

			// Move Down
			control.container.on(
				"click",
				".customify--repeater-item .customify--down",
				function (e) {
					e.preventDefault();
					var n = $(
						".customify--repeater-items .customify--repeater-item",
						control.container
					).length;
					var i = $(this).closest(".customify--repeater-item");
					var index = i.index();
					if (index < n - 1) {
						var down = i.next();
						i.insertAfter(down);
						control.getValue();
					}
				}
			);

			/**
			 * @TODO: Translateable live title if not addable
			 */
			if (!control.params.addable) {
				if (control.params.live_title_field) {
					var _titles = {};
					_.each(control.params.default, function (_value) {
						if (
							!_.isUndefined(_value._key) &&
							!_.isUndefined(
								_value[control.params.live_title_field]
							)
						) {
							_titles[_value._key] =
								_value[control.params.live_title_field];
						}
					});

					_.each(control.params.value, function (_value, index) {
						if (!_.isUndefined(_titles[_value._key])) {
							control.params.value[index][
								control.params.live_title_field
							] = _titles[_value._key];
						}
					});
				}
			}

			// Add item when customizer loaded
			if (_.isArray(control.params.value)) {
				_.each(control.params.value, function (itemValue) {
					control.addRepeaterItem(itemValue);
				});
				control.getValue(false);
			}
			control.limitRepeaterItems();

			// Toggle visibility
			control.container.on(
				"change",
				".customify--repeater-item .r-visible-input",
				function (e) {
					e.preventDefault();
					var p = $(this).closest(".customify--repeater-item");
					if ($(this).is(":checked")) {
						p.removeClass("item---visible-hidden");
					} else {
						p.addClass("item---visible-hidden");
					}
				}
			);

			// Toggle
			if (!control.params.title_only) {
				control.container.on(
					"click",
					".customify--repeater-item-toggle, .customify--repeater-live-title",
					function (e) {
						e.preventDefault();
						var p = $(this).closest(".customify--repeater-item");
						p.toggleClass("customify--open");
					}
				);
			}

			// Remove
			control.container.on("click", ".customify--remove", function (e) {
				e.preventDefault();
				var p = $(this).closest(".customify--repeater-item");
				p.remove();
				$document.trigger("customify/customizer/repeater/remove", [
					control
				]);
				control.getValue();
				control.limitRepeaterItems();
			});

			var defaultValue = {};
			_.each(control.params.fields, function (f, k) {
				defaultValue[f.name] = null;
				if (!_.isUndefined(f.default)) {
					defaultValue[f.name] = f.default;
				}
			});

			// Add Item
			control.container.on(
				"click",
				".customify--repeater-add-new",
				function (e) {
					e.preventDefault();
					if (!$(this).hasClass("disabled")) {
						control.addRepeaterItem(defaultValue);
						control.getValue();
						control.limitRepeaterItems();
					}
				}
			);
		}
	};

	var customify_control = function (control) {
		control = _.extend(control, customify_controlConstructor);
		control.init();
	};
	//---------------------------------------------------------------------------

	wp.customize.controlConstructor.customify = wp.customize.Control.extend({
		ready: function () {
			customify_controls_list[this.id] = this;
		}
	});

	var IconPicker = {
		pickingEl: null,
		listIcons: null,
		render: function (list_icons) {
			var that = this;
			if (!_.isUndefined(list_icons) && !_.isEmpty(list_icons)) {
				_.each(list_icons, function (icon_config, font_type) {
					$("#customify--sidebar-icon-type").append(
						' <option value="' +
						font_type +
						'">' +
						icon_config.name +
						"</option>"
					);
					that.addCSS(icon_config, font_type);
					that.addIcons(icon_config, font_type);
				});
			}
		},

		addCSS: function (icon_config, font_type) {

			if (typeof (icon_config.url) === 'object') {

				$.each(icon_config.url, function (index, value) {
					if (!$("#font-icon-" + index).length) {
						$("head").append(
							"<link rel='stylesheet' id='font-icon-" +
							index +
							"'  href='" +
							value +
							"' type='text/css' media='all' />"
						);
					}
				});
			} else {
				if (!$("#font-icon-" + font_type).length) {
					$("head").append(
						"<link rel='stylesheet' id='font-icon-" +
						font_type +
						"'  href='" +
						icon_config.url +
						"' type='text/css' media='all' />"
					);
				}

			}

		},

		addIcons: function (icon_config, font_type) {
			var icon_html =
				'<ul class="customify--list-icons icon-' +
				font_type +
				'" data-type="' +
				font_type +
				'">';
			_.each(icon_config.icons, function (icon_class, i) {
				var class_name = "";
				if (icon_config.class_config) {
					class_name = icon_config.class_config.replace(
						/__icon_name__/g,
						icon_class
					);
				} else {
					class_name = icon_class;
				}

				icon_html +=
					'<li title="' +
					icon_class +
					'" data-type="' +
					font_type +
					'" data-icon="' +
					class_name +
					'"><span class="icon-wrapper"><i class="' +
					class_name +
					'"></i></span></li>';
			});
			icon_html += "</ul>";

			$("#customify--icon-browser").append(icon_html);
		},
		changeType: function () {
			$document.on("change", "#customify--sidebar-icon-type", function () {
				var type = $(this).val();
				if (!type || type == "all") {
					$("#customify--icon-browser .customify--list-icons").show();
				} else {
					$("#customify--icon-browser .customify--list-icons").hide();
					$(
						"#customify--icon-browser .customify--list-icons.icon-" +
						type
					).show();
				}
			});
		},
		show: function () {
			var controlWidth = $("#customize-controls").width();
			if (!is_rtl) {
				$("#customify--sidebar-icons")
					.css("left", controlWidth)
					.addClass("customify--active");
			} else {
				$("#customify--sidebar-icons")
					.css("right", controlWidth)
					.addClass("customify--active");
			}
		},
		close: function () {
			if (!is_rtl) {
				$("#customify--sidebar-icons")
					.css("left", -300)
					.removeClass("customify--active");
			} else {
				$("#customify--sidebar-icons")
					.css("right", -300)
					.removeClass("customify--active");
			}
			$(".customify--icon-picker").removeClass("customify--icon-picking");
			this.pickingEl = null;
		},
		autoClose: function () {
			var that = this;
			$document.on("click", function (event) {
				if (
					!$(event.target).closest(".customify--icon-picker").length
				) {
					if (
						!$(event.target).closest("#customify--sidebar-icons")
							.length
					) {
						that.close();
					}
				}
			});

			$("#customify--sidebar-icons .customize-controls-icon-close").on(
				"click",
				function () {
					that.close();
				}
			);

			$document.on("keyup", function (event) {
				if (event.keyCode === 27) {
					that.close();
				}
			});
		},
		picker: function () {
			var that = this;

			var open = function ($el) {
				if (that.pickingEl) {
					that.pickingEl.removeClass("customify--icon-picking");
				}
				that.pickingEl = $el.closest(".customify--icon-picker");
				that.pickingEl.addClass("customify--picking-icon");
				that.show();
			};

			$document.on(
				"click",
				".customify--icon-picker .customify--pick-icon",
				function (e) {
					e.preventDefault();
					var button = $(this);
					if (_.isNull(that.listIcons)) {
						that.ajaxLoad(function () {
							open(button);
						});
					} else {
						open(button);
					}
				}
			);

			$document.on("click", "#customify--icon-browser li", function (e) {
				e.preventDefault();
				var li = $(this);
				var icon_preview = li.find("i").clone();
				var icon = li.attr("data-icon") || "";
				var type = li.attr("data-type") || "";
				$(".customify--input-icon-type", that.pickingEl).val(type);
				$(".customify--input-icon-name", that.pickingEl)
					.val(icon)
					.trigger("change");
				$(".customify--icon-preview-icon", that.pickingEl).html(
					icon_preview
				);

				that.close();
			});

			// remove
			$document.on(
				"click",
				".customify--icon-picker .customify--icon-remove",
				function (e) {
					e.preventDefault();
					if (that.pickingEl) {
						that.pickingEl.removeClass("customify--icon-picking");
					}
					that.pickingEl = $(this).closest(".customify--icon-picker");
					that.pickingEl.addClass("customify--picking-icon");

					$(".customify--input-icon-type", that.pickingEl).val("");
					$(".customify--input-icon-name", that.pickingEl)
						.val("")
						.trigger("change");
					$(".customify--icon-preview-icon", that.pickingEl).html("");
				}
			);
		},

		ajaxLoad: function (cb) {
			var that = this;
			$.get(
				Customify_Control_Args.ajax,
				{
					action: "customify/customizer/ajax/get_icons",
					wp_customize: "on",
					_nonce: _wpCustomizeSettings.nonce.preview,
					customize_theme: _wpCustomizeSettings.theme.stylesheet
				},
				function (res) {
					if (res.success) {
						that.listIcons = res.data;
						that.render(res.data);
						that.changeType();
						that.autoClose();
						if (_.isFunction(cb)) {
							cb();
						}
					}
				}
			);
		},
		init: function () {
			var that = this;
			that.ajaxLoad();
			that.picker();
			// Search icon
			$document.on("keyup", "#customify--icon-search", function (e) {
				var v = $(this).val();
				v = v.trim();
				if (v) {
					$("#customify--icon-browser li").hide();
					$(
						"#customify--icon-browser li[data-icon*='" + v + "']"
					).show();
				} else {
					$("#customify--icon-browser li").show();
				}
			});
		}
	};

	var typoControl = setupTypographyControl({ $: $, $document: $document, wpcustomize: wpcustomize, customifyField: customifyField });
	var FontSelector = typoControl.FontSelector;
	var intTypos = typoControl.intTypos;

	//---------------------------------------------------------------------------
	var customifyModal = {
		tabs: {
			normal: "Normal",
			hover: "Hover"
		},
		config: {},
		$el: null,
		container: null,
		controlID: "",
		addFields: function (values) {
			var that = this;
			if (!_.isObject(that.values)) {
				that.values = {};
			}
			that.values = _.defaults(that.values, {});
			var fieldsArea = $(
				".customify-modal-settings--fields",
				that.container
			);
			fieldsArea.html("");

			that.config = _.defaults(that.config, {
				tabs: {}
			});

			var tabsHTML = $('<div class="modal--tabs"></div>');
			var c = 0;
			_.each(that.config.tabs, function (label, key) {
				if (label && _.isObject(that.config[key + "_fields"])) {
					c++;
					tabsHTML.append(
						'<div><span data-tab="' +
						key +
						'" class="modal--tab modal-tab--' +
						key +
						'">' +
						label +
						"</span></div>"
					);
				}
			});

			fieldsArea.append(tabsHTML);
			if (c <= 1) {
				tabsHTML.addClass("customify--hide");
			}
			customifyField.devices = Customify_Control_Args.devices;
			_.each(that.config.tabs, function (label, key) {
				if (
					_.isObject(that.config[key + "_fields"]) &&
					!_.isEmpty(key + "_fields")
				) {
					var content = $(
						'<div class="modal-tab-content modal-tab--' +
						key +
						'"></div>'
					);
					fieldsArea.append(content);
					customifyField.addFields(
						that.config[key + "_fields"],
						that.values[key],
						content,
						function () {
							that.get(_.clone(that.config));
						}
					);
					var fv;
					if (
						_.isUndefined(that.values[key]) ||
						_.isEmpty(that.values[key])
					) {
						fv = {};
						_.each(that.config[key + "_fields"], function (f) {
							fv[f.name] = _.isUndefined(f.default)
								? null
								: f.default;
						});
					} else {
						fv = that.values[key];
					}
					customifyField.initConditional(content, fv);
				}
			});

			$("input, select, textarea", that.container)
				.removeClass("customify-input")
				.addClass("customify-modal-input change-by-js");
			fieldsArea.on(
				"change data-change",
				"input, select, textarea",
				function () {
					that.get(_.clone(that.config));
				}
			);

			that.container.on("click", ".modal--tab", function () {
				var id = $(this).attr("data-tab") || "";
				$(".modal--tabs .modal--tab", that.container).removeClass(
					"tab--active"
				);
				$(this).addClass("tab--active");
				$(".modal-tab-content", that.container).removeClass(
					"tab--active"
				);
				$(
					".modal-tab-content.modal-tab--" + id,
					that.container
				).addClass("tab--active");
			});
			$(".modal--tabs .modal--tab", that.container)
				.eq(0)
				.trigger("click");

			this.container.slideUp(0);
		},

		close: function () {
			var that = this;
			that.container.slideUp(300, function () {
				that.$el.removeClass("modal--opening");
				that.$el.attr("data-opening", "");
				$(".action--reset", that.$el).hide();
			});
		},

		reset: function () {
			var that = this;
			$(".customify-modal-settings", that.$el).remove();
			try {
				var _default = wpcustomize.control(that.controlID).params
					.default;
				that.values = _default;
			} catch (e) {
				that.values = {};
			}
			if (!$(".customify-modal-settings", that.$el).length) {
				var $wrap = $($("#tmpl-customify-modal-settings").html());
				that.container = $wrap;
				this.$el.append($wrap);
				that.addFields();
			} else {
				that.container = $(".customify-modal-settings", that.$el);
			}

			that.$el.addClass("customify-modal--inside");
			that.$el.addClass("modal--opening");
			that.container.show(0);
			$(".customify-hidden-modal-input", that.$el)
				.val(JSON.stringify(that.values))
				.trigger("change");
		},

		get: function (config) {
			var data = {};
			var that = this;
			that.config = config;
			_.each(that.config.tabs, function (label, key) {
				var subdata = {};
				var content = $(
					".modal-tab-content.modal-tab--" + key,
					that.container
				);
				if (_.isObject(that.config[key + "_fields"])) {
					_.each(that.config[key + "_fields"], function (f) {
						subdata[f.name] = customifyField.getValue(
							f,
							$(
								'.customify--group-field[data-field-name="' +
								f.name +
								'"]',
								content
							)
						);
					});
				}
				data[key] = subdata;
				customifyField.initConditional(content, subdata);
			});
			$(".customify-hidden-modal-input", this.$el)
				.val(JSON.stringify(data))
				.trigger("change");
			return data;
		},

		open: function () {
			var that = this;
			var status = that.$el.attr("data-opening") || false;
			if (status !== "opening") {
				that.$el.attr("data-opening", "opening");
				that.values = $(
					".customify-hidden-modal-input",
					that.$el
				).val();
				try {
					that.values = JSON.parse(that.values);
				} catch (e) { }
				that.$el.addClass("customify-modal--inside");
				if (!$(".customify-modal-settings", that.$el).length) {
					var $wrap = $($("#tmpl-customify-modal-settings").html());
					$wrap.hide();
					that.container = $wrap;
					that.$el.append($wrap);
					that.addFields();
				} else {
					that.container = $(".customify-modal-settings", that.$el);
				}

				this.container.slideDown(300);
				this.$el.addClass("modal--opening");
				$(".action--reset", this.$el).show();
			} else {
				this.container.slideUp(300, function () {
					that.$el.attr("data-opening", "");
					$(".customify-modal-settings", that.$el).hide();
					that.$el.removeClass("modal--opening");
					$(".action--reset", that.$el).hide();
				});
			}
		}
	};

	var initModalControls = {};
	var initModal = function () {
		$document.on(
			"click",
			".customize-control-customify-modal .action--edit, .customize-control-customify-modal .action--reset, .customize-control-customify-modal .customify-control-field-header",
			function (e) {
				e.preventDefault();
				var controlID = $(this).attr("data-control") || "";
				if (_.isUndefined(initModalControls[controlID])) {
					var c = wpcustomize.control(controlID);
					if (controlID && !_.isUndefined(c)) {
						var m = _.clone(customifyModal);
						m.config = c.params.fields;
						m.$el = $(this)
							.closest(".customize-control-customify-modal")
							.eq(0);
						m.controlID = controlID;
						initModalControls[controlID] = m;
					}
				}

				if (!_.isUndefined(initModalControls[controlID])) {
					if ($(this).hasClass("action--reset")) {
						initModalControls[controlID].reset();
					} else {
						initModalControls[controlID].open();
					}
				}
			}
		);
	};

	//---------------------------------------------------------------------------
	var customifyStyling = {
		tabs: {
			normal: "Normal",
			hover: "Hover"
		},
		fields: {},
		normal_fields: {},
		hover_fields: {},
		controlID: "",
		$el: "",
		contailner: "",
		activeTab: "",
		setupFields: function (fields, list) {
			var newfs;
			var i;
			var newList = [];
			if (fields === -1) {
				newList = list;
			} else if (fields === false) {
				newList = null;
			} else {
				if (_.isObject(fields)) {
					newfs = {};
					i = 0;
					_.each(list, function (f) {
						if (_.isUndefined(fields[f.name]) || fields[f.name]) {
							newfs[i] = f;
							i++;
						}
					});

					newList = newfs;
				}
			}
			return newList;
		},
		setupConfig: function (tabs, normal_fields, hover_fields) {
			var that = this;
			that.tabs = {};
			that.normal_fields = {};
			that.hover_fields = {};

			that.tabs = _.clone(Customify_Control_Args.styling_config.tabs);
			if (tabs === false) {
				that.tabs["hover"] = false;
			} else if (_.isObject(tabs)) {
				that.tabs = tabs;
			}

			that.normal_fields = that.setupFields(
				normal_fields,
				Customify_Control_Args.styling_config.normal_fields
			);
			that.hover_fields = that.setupFields(
				hover_fields,
				Customify_Control_Args.styling_config.hover_fields
			);
		},
		addFields: function (values) {
			var that = this;
			if (!_.isObject(that.values)) {
				that.values = {};
			}
			that.values = _.defaults(that.values, {
				hover: {},
				normal: {}
			});
			var fieldsArea = $(
				".customify-modal-settings--fields",
				that.container
			);
			fieldsArea.html("");

			var tabsHTML = $('<div class="modal--tabs"></div>');
			var c = 0;
			_.each(that.tabs, function (label, key) {
				if (label && !_.isEmpty(that[key + "_fields"])) {
					c++;
					tabsHTML.append(
						'<div><span data-tab="' +
						key +
						'" class="modal--tab modal-tab--' +
						key +
						'">' +
						label +
						"</span></div>"
					);
				}
			});

			fieldsArea.append(tabsHTML);
			if (c <= 1) {
				tabsHTML.addClass("customify--hide");
			}
			customifyField.devices = Customify_Control_Args.devices;
			_.each(that.tabs, function (label, key) {
				if (
					_.isObject(that[key + "_fields"]) &&
					!_.isEmpty(key + "_fields")
				) {
					var content = $(
						'<div class="modal-tab-content modal-tab--' +
						key +
						'"></div>'
					);
					fieldsArea.append(content);
					customifyField.addFields(
						that[key + "_fields"],
						that.values[key],
						content,
						function () {
							that.get();
						}
					);
					customifyField.initConditional(content, that.values[key]);
				}
			});

			$("input, select, textarea", that.container)
				.removeClass("customify-input")
				.addClass("customify-modal-input change-by-js");

			fieldsArea.on(
				"change data-change",
				"input, select, textarea",
				function () {
					that.get();
				}
			);

			that.container.on("click", ".modal--tab", function () {
				var id = $(this).attr("data-tab") || "";
				$(".modal--tabs .modal--tab", that.container).removeClass(
					"tab--active"
				);
				$(this).addClass("tab--active");
				$(".modal-tab-content", that.container).removeClass(
					"tab--active"
				);
				$(
					".modal-tab-content.modal-tab--" + id,
					that.container
				).addClass("tab--active");
			});
			$(".modal--tabs .modal--tab", that.container)
				.eq(0)
				.trigger("click");
		},

		// Tabs that actually render: label truthy (false/null disables
		// the tab) and a non-empty resolved field list — mirrors the
		// addFields() loop, so trigger rows and tab contents always
		// agree.
		visibleTabs: function () {
			var that = this;
			var out = [];
			_.each(that.tabs, function (label, key) {
				if (label && !_.isEmpty(that[key + "_fields"])) {
					out.push({ key: key, label: label });
				}
			});
			return out;
		},

		// Build the floating panel once (lazily, exactly like the legacy
		// accordion did on first open) or re-acquire it after a rebuild.
		ensurePanel: function () {
			var that = this;
			that.$el.addClass("customify-modal--inside");
			if (!$(".customify-modal-settings", that.$el).length) {
				var $wrap = $($("#tmpl-customify-modal-settings").html());
				that.container = $wrap;
				that.$el.append($wrap);
				that.addFields();
			} else {
				that.container = $(".customify-modal-settings", that.$el);
			}
		},

		// Show one tab's fields inside the popover. The legacy tab bar is
		// still built (hidden by CSS under the trigger chrome) —
		// re-triggering its click handler keeps the tab--active
		// bookkeeping, and with it get(), untouched.
		selectTab: function (key) {
			var that = this;
			that.activeTab = key;
			$(
				'.modal--tab[data-tab="' + key + '"]',
				that.container
			).trigger("click");
			$(".customify-styling-trigger", that.$el)
				.removeClass("is-open")
				.filter('[data-tab="' + key + '"]')
				.addClass("is-open");
		},

		close: function () {
			this.closePopover();
		},

		reset: function () {
			var that = this;

			// Reset is only reachable while the popover is open (the
			// button is hidden otherwise) — rebuilding removes the panel,
			// so re-open it afterwards for continuity.
			var wasOpen = "opening" === that.$el.attr("data-opening");

			$(".customify-modal-settings", that.$el).remove();
			try {
				var _default = wpcustomize.control(that.controlID).params
					.default;
				that.values = _default;
			} catch (e) {
				that.values = {};
			}
			that.ensurePanel();
			$(".customify-hidden-modal-input", that.$el)
				.val(JSON.stringify(that.values))
				.trigger("change");
			if (wasOpen) {
				that.selectTab(that.activeTab);
				that.openPopover();
			}
		},

		get: function () {
			var data = {};
			var that = this;
			_.each(that.tabs, function (label, key) {
				var subdata = {};
				var content = $(
					".modal-tab-content.modal-tab--" + key,
					that.container
				);
				if (_.isObject(that[key + "_fields"])) {
					_.each(that[key + "_fields"], function (f) {
						subdata[f.name] = customifyField.getValue(
							f,
							$(
								'.customify--group-field[data-field-name="' +
								f.name +
								'"]',
								content
							)
						);
					});
				}
				data[key] = subdata;
				customifyField.initConditional(content, subdata);
			});

			$(".customify-hidden-modal-input", this.$el)
				.val(JSON.stringify(data))
				.trigger("change");
			return data;
		},

		// Open the popover anchored under the `tab` trigger row — or
		// close it when that row's popover is already open (the trigger
		// is a toggle). Clicking a sibling row while open just switches
		// the visible tab and re-anchors; the panel itself stays open.
		open: function (tab) {
			var that = this;
			var isOpen = "opening" === that.$el.attr("data-opening");
			if (isOpen && tab === that.activeTab) {
				that.closePopover();
				return;
			}

			that.values = $(
				".customify-hidden-modal-input",
				that.$el
			).val();
			try {
				that.values = JSON.parse(that.values);
			} catch (e) { }
			that.ensurePanel();
			that.selectTab(tab);
			that.openPopover();
		}
	};

	// Popover lifecycle from the shared chrome (one popover at a time
	// across styling AND typography controls). Attached to the base
	// object so the per-control clones made in stylingRuntime() inherit
	// the methods.
	attachPopoverChrome(customifyStyling, {
		$: $,
		anchor: function (that) {
			return $(
				'.customify-styling-trigger[data-tab="' +
				that.activeTab +
				'"]',
				that.$el
			);
		},
		onClose: function (that) {
			$(".customify-styling-trigger", that.$el).removeClass(
				"is-open"
			);
		}
	});

	// ── Styling trigger rows ───────────────────────────────────────────
	// One select-like trigger row per visible tab (Normal / Hover / …);
	// each row previews that tab's saved colors as swatches plus a
	// one-word tail for non-color edits. Chrome only: rows read the
	// hidden input JSON the runtime already round-trips — value plumbing
	// (get()/storage shape) is untouched.

	// Fixed swatch order; color fields added by the styling_config filter
	// keep their config order after these.
	var STYLING_SWATCH_ORDER = [
		"text_color",
		"link_color",
		"bg_color",
		"border_color"
	];

	// First name with a saved value wins — the tail is a single word.
	var STYLING_TAIL_WORDS = [
		["padding", "padding"],
		["margin", "margin"],
		["border_style", "border"],
		["border_width", "border"],
		["border_radius", "radius"],
		["box_shadow", "shadow"],
		["bg_image", "image"],
		["bg_cover", "image"],
		["bg_position", "image"],
		["bg_repeat", "image"],
		["bg_attachment", "image"]
	];

	// Does a saved sub-value hold anything the user actually set? Wrapper
	// keys that exist even on untouched fields (css_ruler's
	// `unit`/`link`, shadow's `inset`, media's `id`/`mime`) are skipped
	// so an empty ruler ({unit:'px', top:'', …}) doesn't count as set.
	var stylingHasValue = function (v) {
		if (v === null || _.isUndefined(v) || false === v) {
			return false;
		}
		if (true === v) {
			return true;
		}
		if (_.isNumber(v)) {
			return true;
		}
		if (_.isString(v)) {
			return $.trim(v) !== "";
		}
		if (_.isObject(v)) {
			var found = false;
			_.each(v, function (sub, key) {
				if (
					found ||
					"unit" === key ||
					"link" === key ||
					"inset" === key ||
					"id" === key ||
					"mime" === key
				) {
					return;
				}
				found = stylingHasValue(sub);
			});
			return found;
		}
		return false;
	};

	// One tab's preview model: { swatches: [{color, title, ring}], meta,
	// isDefault }.
	var stylingTabPreview = function (runtime, key, values, singleTab) {
		var sub =
			_.isObject(values) && _.isObject(values[key])
				? values[key]
				: {};

		// A field only counts when its `required` condition passes against
		// the tab's current values — a value left behind by a gated-off
		// field (e.g. border_color after border_style went back to
		// Default) stays in the JSON but has no effect, so it must not
		// show in the preview either.
		var effective = function (f) {
			if (_.isUndefined(f.required) || _.isEmpty(f.required)) {
				return true;
			}
			try {
				return customifyField.multiple_compare(
					f.required,
					sub,
					false
				);
			} catch (e) {
				return true;
			}
		};

		var colors = [];
		var others = [];
		_.each(runtime[key + "_fields"], function (f) {
			if (!_.isObject(f) || "heading" === f.type || !effective(f)) {
				return;
			}
			if ("color" === f.type) {
				colors.push(f);
			} else {
				others.push(f);
			}
		});

		colors.sort(function (a, b) {
			var ra = _.indexOf(STYLING_SWATCH_ORDER, a.name);
			var rb = _.indexOf(STYLING_SWATCH_ORDER, b.name);
			return (
				(ra === -1 ? STYLING_SWATCH_ORDER.length : ra) -
				(rb === -1 ? STYLING_SWATCH_ORDER.length : rb)
			);
		});

		var swatches = [];
		_.each(colors, function (f) {
			var v = sub[f.name];
			if (_.isString(v) && $.trim(v) !== "" && swatches.length < 4) {
				swatches.push({
					color: v,
					title: (f.label || f.name) + ": " + v,
					ring: String(f.name).indexOf("border") !== -1
				});
			}
		});

		var meta = "";
		var isDefault = false;

		// Single-tab, single-color controls (the Colors-section
		// backgrounds) echo the picked value next to their swatch.
		if (singleTab && 1 === colors.length && 1 === swatches.length) {
			meta = swatches[0].color;
		}

		if (!meta) {
			_.each(STYLING_TAIL_WORDS, function (pair) {
				if (meta) {
					return;
				}
				var f = _.find(others, function (o) {
					return o.name === pair[0];
				});
				if (f && stylingHasValue(sub[f.name])) {
					meta = "+ " + pair[1];
				}
			});
		}
		if (!meta) {
			// Unknown fields (added via the styling_config filter): fall
			// back to the field's own label.
			var known = _.map(STYLING_TAIL_WORDS, function (p) {
				return p[0];
			});
			_.each(others, function (f) {
				if (meta || _.indexOf(known, f.name) !== -1) {
					return;
				}
				if (stylingHasValue(sub[f.name])) {
					meta = "+ " + String(f.label || f.name).toLowerCase();
				}
			});
		}

		if (!swatches.length && !meta) {
			meta = Customify_Control_Args.default_label || "Default";
			isDefault = true;
		}

		return { swatches: swatches, meta: meta, isDefault: isDefault };
	};

	// Skeleton rows are built once per control; repaints only swap the
	// preview/meta contents so focus and the is-open state survive.
	var ensureStylingRows = function (runtime) {
		var $wrap = $(".customify-styling-triggers", runtime.$el);
		if (!$wrap.length || $wrap.children().length) {
			return;
		}
		var tabs = runtime.visibleTabs();
		var single = 1 === tabs.length;
		_.each(tabs, function (t) {
			var $row = $(
				'<a href="#" class="customify-styling-trigger"></a>'
			).attr("data-tab", t.key);
			// Single-tab controls render a label-less row (the control
			// title right above already names it); `_` is the explicit
			// no-label sentinel some configs use.
			if (!single && "_" !== t.label) {
				$row.append(
					$(
						'<span class="customify-trigger--label"></span>'
					).text(t.label)
				);
			}
			$row.append('<span class="customify-trigger--preview"></span>');
			$row.append('<span class="customify-trigger--meta"></span>');
			if (single || "_" === t.label) {
				$row.append(
					'<span class="customify-trigger--spacer"></span>'
				);
			}
			$row.append(
				'<span class="customify-trigger--arrow dashicons dashicons-arrow-down-alt2"></span>'
			);
			$wrap.append($row);
		});
	};

	var paintStylingRows = function (runtime) {
		var $wrap = $(".customify-styling-triggers", runtime.$el);
		if (!$wrap.length) {
			return;
		}
		var values = {};
		try {
			values = JSON.parse(
				$(".customify-hidden-modal-input", runtime.$el).val() || ""
			);
		} catch (e) { }
		var tabs = runtime.visibleTabs();
		var single = 1 === tabs.length;
		_.each(tabs, function (t) {
			var $row = $(
				'.customify-styling-trigger[data-tab="' + t.key + '"]',
				$wrap
			);
			if (!$row.length) {
				return;
			}
			var view = stylingTabPreview(runtime, t.key, values, single);
			var $preview = $(".customify-trigger--preview", $row).empty();
			_.each(view.swatches, function (s) {
				var $sw = $(
					'<span class="customify-trigger--swatch"><i></i></span>'
				).attr("title", s.title);
				if (s.ring) {
					// Border colors render as a ring so they don't read
					// as a fill; the inset shadow carries the color.
					$sw.addClass("is-ring");
					$("i", $sw).css(
						"box-shadow",
						"inset 0 0 0 3px " + s.color
					);
				} else {
					$("i", $sw).css("background-color", s.color);
				}
				$preview.append($sw);
			});
			$(".customify-trigger--meta", $row)
				.text(view.meta)
				.toggleClass("is-default", view.isDefault);
		});
	};

	var initStylingControls = {};
	// Create (or fetch) the runtime for a styling control li. Mirrors the
	// legacy lazy click-init, but also runs at batch-init time so the
	// trigger rows can paint before any interaction.
	var stylingRuntime = function ($el) {
		var controlID = ($el.attr("id") || "").replace(
			/^customize-control-/,
			""
		);
		if (!controlID) {
			return null;
		}
		if (!_.isUndefined(initStylingControls[controlID])) {
			return initStylingControls[controlID];
		}
		var c = wpcustomize.control(controlID);
		if (_.isUndefined(c)) {
			return null;
		}
		var s = _.clone(customifyStyling);
		var tabs = null,
			normal_fields = -1,
			hover_fields = -1;
		if (
			!_.isUndefined(c.params.fields) &&
			_.isObject(c.params.fields)
		) {
			if (!_.isUndefined(c.params.fields.tabs)) {
				tabs = c.params.fields.tabs;
			}
			if (!_.isUndefined(c.params.fields.normal_fields)) {
				normal_fields = c.params.fields.normal_fields;
			}
			if (!_.isUndefined(c.params.fields.hover_fields)) {
				hover_fields = c.params.fields.hover_fields;
			}
		}
		s.$el = $el;
		s.setupConfig(tabs, normal_fields, hover_fields);
		s.controlID = controlID;
		initStylingControls[controlID] = s;
		return s;
	};

	var initStyling = function () {
		// Trigger rows: open that row's tab in the floating popover.
		$document.on(
			"click",
			".customize-control-customify-styling .customify-styling-trigger",
			function (e) {
				e.preventDefault();
				var s = stylingRuntime(
					$(this)
						.closest(".customize-control-customify-styling")
						.eq(0)
				);
				if (s) {
					s.open($(this).attr("data-tab") || "");
				}
			}
		);

		// Reset keeps its legacy delegated binding (the button is only
		// visible while the popover is open).
		$document.on(
			"click",
			".customize-control-customify-styling .action--reset",
			function (e) {
				e.preventDefault();
				var s = stylingRuntime(
					$(this)
						.closest(".customize-control-customify-styling")
						.eq(0)
				);
				if (s) {
					s.reset();
				}
			}
		);

		// Every value write round-trips through the hidden input —
		// repaint the row previews there.
		$document.on(
			"change data-change",
			".customize-control-customify-styling .customify-hidden-modal-input",
			function () {
				var s = stylingRuntime(
					$(this)
						.closest(".customize-control-customify-styling")
						.eq(0)
				);
				if (s) {
					ensureStylingRows(s);
					paintStylingRows(s);
				}
			}
		);

		// External setting write → refreshFromSetting() re-rendered the
		// field DOM (trigger rows included) and the hidden input now
		// holds the new value; the floating panel (parked on the li,
		// outside the re-rendered area) is stale — drop it so the next
		// open rebuilds from the fresh value.
		$document.on(
			"customify/control/refreshed",
			".customize-control-customify-styling",
			function () {
				var s = stylingRuntime($(this));
				if (!s) {
					return;
				}
				if ("opening" === s.$el.attr("data-opening")) {
					s.closePopover();
				}
				$(".customify-modal-settings", s.$el).remove();
				s.container = null;
				ensureStylingRows(s);
				paintStylingRows(s);
			}
		);

		// First paint — controls are batch-initialized at document.ready
		// before initStyling() runs, so every styling control's hidden
		// input + trigger wrap exist by now.
		$(".customize-control-customify-styling").each(function () {
			var s = stylingRuntime($(this));
			if (s) {
				ensureStylingRows(s);
				paintStylingRows(s);
			}
		});
	};

	//---------------------------------------------------------------------------

	wpcustomize.bind("ready", function (e, b) {
		$document.on("customify/customizer/device/change", function (e, device) {
			$(".customify--device-select a").removeClass("customify--active");
			if (device != "mobile") {
				$(".customify--device-mobile").addClass("customify--hide");
				$(".customify--device-general").removeClass("customify--hide");
				$(".customify--tab-device-general").addClass(
					"customify--active"
				);
			} else {
				$(".customify--device-general").addClass("customify--hide");
				$(".customify--device-mobile").removeClass("customify--hide");
				$(".customify--tab-device-mobile").addClass(
					"customify--active"
				);
			}
		});

		$document.on("click", ".customify--tab-device-mobile", function (e) {
			e.preventDefault();
			$document.trigger("customify/customizer/device/change", ["mobile"]);
		});

		$document.on("click", ".customify--tab-device-general", function (e) {
			e.preventDefault();
			$document.trigger("customify/customizer/device/change", [
				"general"
			]);
		});

		$(".accordion-section").each(function () {
			var s = $(this);
			var t = $(".customify--device-select", s).first();
			$(".customize-section-title", s).append(t);
		});

		// Devices Switcher
		$document.on("click", ".customify-devices button", function (e) {
			e.preventDefault();
			var device = $(this).attr("data-device") || "";
			//console.log('Device', device);
			$(
				'#customize-footer-actions .devices button[data-device="' +
				device +
				'"]'
			).trigger("click");
		});

		// Devices Switcher
		$document.on("change", ".customify--field input:checkbox", function (e) {
			if ($(this).is(":checked")) {
				$(this)
					.parent()
					.addClass("customify--checked");
			} else {
				$(this)
					.parent()
					.removeClass("customify--checked");
			}
		});

		// Setup conditional
		var ControlConditional = function (decodeValue) {
			if (_.isUndefined(decodeValue)) {
				decodeValue = false;
			}
			var allValues = wpcustomize.get();
			// console.log( 'ALL Control Values', allValues );
			_.each(allValues, function (value, id) {
				var control = wpcustomize.control(id);
				if (!_.isUndefined(control)) {
					if (control.params.type == "customify") {
						if (!_.isEmpty(control.params.required)) {
							var check = false;
							check = control.multiple_compare(
								control.params.required,
								allValues,
								decodeValue
							);
							if (!check) {
								control.container.addClass("customify--hide");
							} else {
								control.container.removeClass(
									"customify--hide"
								);
							}
						}
					}
				}
			});
		};

		$document.ready(function () {
			_.each(customify_controls_list, function (c, k) {
				new customify_control(c);
			});

			ControlConditional(false);
			$document.on("customify/customizer/value_changed", function () {
				ControlConditional(true);
			});

			IconPicker.init();
			FontSelector.init();
			initStyling();
			initModal();
			intTypos();
			// Expose helpers used by React-based controls (e.g. Column Settings)
			// to drive the jQuery slider + css_ruler renderers.
			window.customifyField = customifyField;
			observeAndMountColumnsSettings();
		});

		// Add reset button to sections
		wpcustomize.section.each(function (section) {
			if (
				section.params.type == "section" ||
				section.params.type == "customify_section"
			) {
				section.container
					.find(
						".customize-section-description-container .customize-section-title"
					)
					.append(
						'<button data-section="' +
						section.id +
						'" type="button" title="' +
						Customify_Control_Args.reset +
						'" class="customize--reset-section" aria-expanded="false"><span class="screen-reader-text">' +
						Customify_Control_Args.reset +
						"</span></button>"
					);
			}
		});

		// Remove checked align
		$document.on("dblclick", ".customify-text-align label", function (e) {
			var input = $(this).find('input[type="radio"]');
			if (input.length) {
				if (input.is(":checked")) {
					input.removeAttr("checked");
					input.trigger("data-change");
				}
			}
		});

		$document.on("click", ".customize--reset-section", function (e) {
			e.preventDefault();
			if ($(this).hasClass("loading")) {
				return;
			}

			if (!confirm(Customify_Control_Args.confirm_reset)) {
				return;
			}

			$(this).addClass("loading");
			var section = $(this).attr("data-section") || "";
			var urlParser = _.clone(window.location);

			if (section) {
				var setting_keys = [];
				var controls = wp.customize.section(section).controls();
				_.each(controls, function (c, index) {
					wpcustomize(c.id).set("");
					setting_keys[index] = c.id;
				});

				$.post(
					ajaxurl,
					{
						action: "customify__reset_section",
						section: section,
						settings: setting_keys,
						nonce: Customify_Control_Args.nonce
					},
					function () {
						$(window).off("beforeunload.customize-confirm");
						top.location.href =
							urlParser.origin +
							urlParser.pathname +
							"?autofocus[section]=" +
							section +
							"&url=" +
							encodeURIComponent(
								wpcustomize.previewer.previewUrl.get()
							);
					}
				);
			}
		});

		/**
		 * Image Select disable click
		 */
		$document.on("click", ".customify-radio-list p", function (e) {
			var id =
				$(this)
					.find("input")
					.attr("data-name") || false;
			var disabled = $(this).hasClass("input-disabled");

			if (id) {
				var setting = wp.customize(id);
				var control = wp.customize.control(id);
				var code = "noti_" + id;
				var msg = "";
				if (control.params._pro && control.params.disabled_pro_msg) {
					msg = control.params.disabled_pro_msg;
				} else if (control.params.disabled_msg) {
					msg = control.params.disabled_msg;
				}
				if (msg) {
					if (disabled) {
						setting.notifications.add(
							code,
							new wp.customize.Notification(code, {
								type: "warning",
								message: msg
							})
						);
					} else {
						setting.notifications.remove(code);
					}
				}
			}
		});

		/**
		 * When panel open
		 */
		_.each(Customify_Control_Args.panel_urls, function (url, id) {
			if (url) {
				wp.customize.panel(id, function (panel) {
					panel.expanded.bind(function (isExpanded) {
						if (isExpanded) {
							wp.customize.previewer.previewUrl.set(url);
						}
					});
				});
			}
		});

		_.each(Customify_Control_Args.section_urls, function (url, id) {
			if (url) {
				wp.customize.section(id, function (section) {
					section.expanded.bind(function (isExpanded) {
						if (isExpanded) {
							wp.customize.previewer.previewUrl.set(url);
						}
					});
				});
			}
		});
	}); // end customize ready
})(jQuery, wp.customize || null);
