/**
 * Schema.org Visual Injector — Admin Metabox JS
 *
 * Handles dynamic property switching, nested group visibility,
 * FAQ entity management, and custom property management.
 *
 * @package Schema_Org_Visual_Injector
 */

/* global jQuery */
(function ($) {
	'use strict';

	var SOVIMetabox = {

		/**
		 * Default properties per Schema type (mirrors PHP dictionary).
		 */
		typeProperties: {
			Article:        ['name', 'description', 'image', 'url', 'author', 'datePublished', 'dateModified', 'headline'],
			Book:           ['name', 'description', 'image', 'url', 'isbn', 'author', 'bookFormat'],
			Event:          ['name', 'description', 'image', 'url', 'startDate', 'endDate'],
			FAQPage:        ['name', 'description', 'image', 'url'],
			LocalBusiness:  ['name', 'description', 'image', 'url', 'telephone', 'priceRange'],
			Organization:   ['name', 'description', 'image', 'url', 'logo', 'sameAs', 'contactPoint'],
			Person:         ['name', 'description', 'image', 'url', 'jobTitle', 'sameAs', 'worksFor'],
			Product:        ['name', 'description', 'image', 'url', 'sku', 'priceCurrency', 'availability'],
			Recipe:         ['name', 'description', 'image', 'url', 'cookTime', 'recipeIngredient', 'recipeInstructions'],
			Service:        ['name', 'description', 'image', 'url', 'areaServed', 'serviceType'],
			WebPage:        ['name', 'description', 'image', 'url', 'breadcrumb', 'datePublished', 'dateModified']
		},

		init: function () {
			$(document).on('change', '#sovi_schema_type', this.onTypeChange);
			$(document).on('click', '#sovi-add-custom-prop', this.addCustomProperty);
			$(document).on('click', '.sovi-remove-custom-prop', this.removeCustomProperty);
			$(document).on('click', '#sovi-add-faq-row', this.addFaqRow);
			$(document).on('click', '.sovi-remove-faq-row', this.removeFaqRow);
		},

		/**
		 * Rebuild property fields, show/hide nested groups, and toggle FAQ section
		 * when the Schema type changes.
		 */
		onTypeChange: function () {
			var type = $(this).val();
			var props = SOVIMetabox.typeProperties[type] || SOVIMetabox.typeProperties['Article'];

			// Rebuild standard property fields.
			var $container = $('#sovi-properties-container');
			$container.find('h4').nextAll('p').remove();

			$.each(props, function (index, prop) {
				var $p = $('<p></p>');
				var $label = $('<label></label>')
					.attr('for', 'sovi_prop_' + prop)
					.text(prop + ':');
				var $input = $('<input>')
					.attr({
						type: 'text',
						id: 'sovi_prop_' + prop,
						name: 'sovi_payload[' + prop + ']',
						class: 'widefat sovi-prop-input'
					});
				$p.append($label).append('<br/>').append($input);
				$container.append($p);
			});

			// Show/hide nested property groups.
			$('.sovi-nested-group').hide();
			$('.sovi-nested-group[data-type="' + type + '"]').show();

			// Show/hide FAQ section.
			if (type === 'FAQPage') {
				$('#sovi-faq-container').show();
			} else {
				$('#sovi-faq-container').hide();
			}
		},

		/**
		 * Add a new FAQ question/answer row.
		 */
		addFaqRow: function (e) {
			e.preventDefault();
			var $list = $('#sovi-faq-list');
			var index = $list.find('.sovi-faq-row').length;

			var $row = $(
				'<div class="sovi-faq-row" data-index="' + index + '">' +
					'<p>' +
						'<label><strong>Question:</strong></label>' +
						'<input type="text" name="sovi_faq_question[]" value="" class="widefat" placeholder="e.g., What is Schema.org?" />' +
					'</p>' +
					'<p>' +
						'<label><strong>Answer:</strong></label>' +
						'<textarea name="sovi_faq_answer[]" rows="3" class="widefat" placeholder="e.g., Schema.org is a collaborative vocabulary..."></textarea>' +
					'</p>' +
					'<p><button type="button" class="button sovi-remove-faq-row">Remove</button></p>' +
					'<hr/>' +
				'</div>'
			);
			$list.append($row);
		},

		/**
		 * Remove an FAQ question/answer row.
		 */
		removeFaqRow: function (e) {
			e.preventDefault();
			var $rows = $('.sovi-faq-row');
			$(this).closest('.sovi-faq-row').remove();

			// Ensure at least one row remains.
			if ($rows.length <= 1) {
				SOVIMetabox.addFaqRow(e);
			}
		},

		/**
		 * Add a new custom property row.
		 */
		addCustomProperty: function (e) {
			e.preventDefault();
			var $row = $(
				'<p class="sovi-custom-prop-row">' +
					'<input type="text" name="sovi_custom_key[]" value="" placeholder="Property name" class="regular-text" />' +
					'<input type="text" name="sovi_custom_val[]" value="" placeholder="Value (supports tokens)" class="regular-text" />' +
					'<button type="button" class="button sovi-remove-custom-prop">&times;</button>' +
				'</p>'
			);
			$('#sovi-custom-props-list').append($row);
		},

		/**
		 * Remove a custom property row.
		 */
		removeCustomProperty: function (e) {
			e.preventDefault();
			$(this).closest('.sovi-custom-prop-row').remove();
		}
	};

	$(document).ready(function () {
		SOVIMetabox.init();
	});

})(jQuery);
