(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
		// ===== Inclusions/Exclusions/Add-ons Dynamic Lists =====
		
		// Add item to inclusions/exclusions
		$(document).on('click', '.ctm-add-item', function() {
			var target = $(this).data('target');
			var type = $(this).data('type');
			var container = $('#' + target);
			
			var newItem;
			if (type === 'addon') {
				// Add-on with name and price
				newItem = $('<div class="ctm-list-item">' +
					'<input type="text" class="ctm-item-input ctm-addon-name" name="_addons_name[]" value="" placeholder="Add-on name" style="width: 40%;">' +
					'<input type="number" step="0.01" class="ctm-item-input ctm-addon-price" name="_addons_price[]" value="" placeholder="Price" style="width: 20%;">' +
					'<button type="button" class="button ctm-remove-item">Remove</button>' +
				'</div>');
			} else {
				// Simple inclusion/exclusion
				var inputName = target === 'ctm-inclusions-list' ? '_inclusions_items[]' : '_exclusions_items[]';
				var placeholder = target === 'ctm-inclusions-list' ? 'e.g., Hotel accommodation' : 'e.g., Airfare';
				newItem = $('<div class="ctm-list-item">' +
					'<input type="text" class="ctm-item-input" name="' + inputName + '" value="" placeholder="' + placeholder + '">' +
					'<button type="button" class="button ctm-remove-item">Remove</button>' +
				'</div>');
			}
			
			container.append(newItem);
		});
		
		// Remove item from list
		$(document).on('click', '.ctm-remove-item', function() {
			var item = $(this).closest('.ctm-list-item');
			var container = item.parent();
			
			// Don't remove if it's the last item
			if (container.children('.ctm-list-item').length > 1) {
				item.remove();
			} else {
				// Clear the inputs instead
				item.find('input').val('');
			}
		});
		
		// ===== Seasonal Pricing Dynamic List =====
		
		// Add seasonal pricing row
		$(document).on('click', '.ctm-add-season', function() {
			var newSeason = $('<div class="ctm-seasonal-item">' +
				'<input type="text" class="ctm-season-name" name="_seasonal_name[]" value="" placeholder="Season name" style="width: 20%;">' +
				'<input type="date" class="ctm-season-start" name="_seasonal_start[]" value="" placeholder="Start date">' +
				'<input type="date" class="ctm-season-end" name="_seasonal_end[]" value="" placeholder="End date">' +
				'<input type="number" step="0.01" class="ctm-season-price" name="_seasonal_price[]" value="" placeholder="Price" style="width: 15%;">' +
				'<button type="button" class="button ctm-remove-season">Remove</button>' +
			'</div>');
			
			$('#ctm-seasonal-pricing-list').append(newSeason);
		});
		
		// Remove seasonal pricing row
		$(document).on('click', '.ctm-remove-season', function() {
			$(this).closest('.ctm-seasonal-item').remove();
		});
		
		// ===== Itinerary JSON Helpers =====
		
		// Load example itinerary
		$('#ctm-load-itinerary-example').on('click', function(e) {
			e.preventDefault();
			var exampleItinerary = [
				{
					"day": 1,
					"title": "Arrival & Beach Exploration",
					"description": "Welcome to Cayman Islands",
					"activities": [
						{
							"start_time": "09:00",
							"end_time": "10:30",
							"title": "Airport Pickup",
							"location": "Owen Roberts International Airport",
							"description": "Meet and greet with tour guide"
						},
						{
							"start_time": "11:00",
							"end_time": "13:00",
							"title": "Seven Mile Beach",
							"location": "Seven Mile Beach, Grand Cayman",
							"description": "Relax at the world-famous beach"
						},
						{
							"start_time": "14:00",
							"end_time": "16:00",
							"title": "George Town Shopping",
							"location": "George Town",
							"description": "Explore local shops and duty-free stores"
						}
					]
				},
				{
					"day": 2,
					"title": "Marine Adventure",
					"description": "Explore underwater wonders",
					"activities": [
						{
							"start_time": "08:00",
							"end_time": "12:00",
							"title": "Stingray City Tour",
							"location": "Stingray City Sandbar",
							"description": "Swim with friendly stingrays in crystal clear waters"
						},
						{
							"start_time": "13:00",
							"end_time": "15:00",
							"title": "Coral Gardens Snorkeling",
							"location": "Coral Gardens",
							"description": "Discover colorful marine life"
						}
					]
				},
				{
					"day": 3,
					"title": "Cultural & Nature Day",
					"description": "Experience local culture and natural beauty",
					"activities": [
						{
							"start_time": "09:00",
							"end_time": "11:00",
							"title": "Queen Elizabeth II Botanic Park",
							"location": "Frank Sound Road, Grand Cayman",
							"description": "Explore native flora and fauna"
						},
						{
							"start_time": "12:00",
							"end_time": "14:00",
							"title": "Local Cuisine Lunch",
							"location": "Traditional Caymanian Restaurant",
							"description": "Taste authentic Caymanian dishes"
						},
						{
							"start_time": "15:00",
							"end_time": "17:00",
							"title": "Rum Point Relaxation",
							"location": "Rum Point, North Side",
							"description": "Enjoy the peaceful beach and famous mudslides"
						}
					]
				}
			];
			
			$('#_itinerary').val(JSON.stringify(exampleItinerary, null, 2));
			alert('Example itinerary loaded! You can now customize it to your needs.');
		});
		
		// Format JSON
		$('#ctm-format-itinerary-json').on('click', function(e) {
			e.preventDefault();
			var textarea = $('#_itinerary');
			try {
				var json = JSON.parse(textarea.val());
				textarea.val(JSON.stringify(json, null, 2));
				$('#ctm-itinerary-validation').html('<span style="color: green;">✓ JSON formatted successfully</span>');
				setTimeout(function() {
					$('#ctm-itinerary-validation').html('');
				}, 3000);
			} catch (error) {
				$('#ctm-itinerary-validation').html('<span style="color: red;">✗ Invalid JSON: ' + error.message + '</span>');
			}
		});
		
		// Validate JSON
		$('#ctm-validate-itinerary-json').on('click', function(e) {
			e.preventDefault();
			var textarea = $('#_itinerary');
			try {
				var json = JSON.parse(textarea.val());
				
				// Basic validation
				if (!Array.isArray(json)) {
					throw new Error('Itinerary must be an array');
				}
				
				// Validate structure
				var hasErrors = false;
				json.forEach(function(day, index) {
					if (!day.day) {
						throw new Error('Day ' + (index + 1) + ' is missing "day" field');
					}
					if (!day.activities || !Array.isArray(day.activities)) {
						throw new Error('Day ' + day.day + ' is missing "activities" array');
					}
				});
				
				$('#ctm-itinerary-validation').html('<span style="color: green;">✓ JSON is valid! ' + json.length + ' day(s) defined</span>');
				setTimeout(function() {
					$('#ctm-itinerary-validation').html('');
				}, 5000);
			} catch (error) {
				$('#ctm-itinerary-validation').html('<span style="color: red;">✗ ' + error.message + '</span>');
			}
		});
		
		// ===== Schedule Template Example =====
		
		$('#ctm-load-schedule-example').on('click', function(e) {
			e.preventDefault();
			
			// Example schedule data
			var exampleSchedule = [
				{
					"time": "08:00",
					"title": "Morning Pickup",
					"description": "Pickup from hotel lobby"
				},
				{
					"time": "09:00",
					"title": "Departure to Activity Site",
					"description": "Scenic drive along coastal road"
				},
				{
					"time": "10:00",
					"title": "Main Activity",
					"description": "Primary tour activity begins"
				},
				{
					"time": "12:30",
					"title": "Lunch Break",
					"description": "Local cuisine at beachfront restaurant"
				},
				{
					"time": "14:00",
					"title": "Optional Activity",
					"description": "Free time for swimming or relaxation"
				},
				{
					"time": "16:00",
					"title": "Return Journey",
					"description": "Return to hotel with photo stop opportunities"
				},
				{
					"time": "17:30",
					"title": "Hotel Drop-off",
					"description": "End of tour day"
				}
			];
			
			// Use the schedule helper if available
			if (window.ctmSchedule && window.ctmSchedule.loadFromString) {
				window.ctmSchedule.loadFromString(JSON.stringify(exampleSchedule));
				alert('Example schedule loaded! Click "Add Schedule Item" to add more or modify existing items.');
			}
		});
	});

})( jQuery );
