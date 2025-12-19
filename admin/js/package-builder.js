// File: admin/js/package-builder.js

(function($) {
    'use strict';
    
    var CTMPackageBuilder = {
        
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.initFlatpickr();
            this.loadSeasonalPricing();
        },
        
        bindEvents: function() {
            // Add day to itinerary
            $(document).on('click', '.ctm-add-day', this.addDay.bind(this));
            
            // Add activity to day
            $(document).on('click', '.ctm-add-activity', this.addActivity.bind(this));
            
            // Remove activity
            $(document).on('click', '.ctm-remove-activity', this.removeActivity.bind(this));
            
            // Remove day
            $(document).on('click', '.ctm-remove-day', this.removeDay.bind(this));
            
            // Add inclusion
            $(document).on('click', '.ctm-add-inclusion', this.addInclusion.bind(this));
            
            // Remove inclusion
            $(document).on('click', '.ctm-remove-inclusion', this.removeInclusion.bind(this));
            
            // Add seasonal pricing
            $(document).on('click', '.ctm-add-seasonal-price', this.addSeasonalPrice.bind(this));
            
            // Remove seasonal pricing
            $(document).on('click', '.ctm-remove-seasonal-price', this.removeSeasonalPrice.bind(this));
            
            // Save itinerary via AJAX
            $(document).on('click', '.ctm-save-itinerary', this.saveItinerary.bind(this));
            
            // Duplicate package
            $(document).on('click', '.ctm-duplicate-package', this.duplicatePackage.bind(this));
            
            // Auto-generate package code
            $('#generate-package-code').on('click', this.generatePackageCode.bind(this));
            
            // Calculate price preview
            $('.ctm-price-input').on('change keyup', this.calculatePricePreview.bind(this));
        },
        
        initSortable: function() {
            $('.ctm-activities-list').sortable({
                handle: '.ctm-activity-item',
                placeholder: 'ctm-activity-placeholder',
                opacity: 0.7,
                update: function() {
                    CTMPackageBuilder.updateItineraryJSON();
                }
            });
            
            $('.ctm-inclusions-list').sortable({
                handle: '.ctm-inclusion-item',
                placeholder: 'ctm-inclusion-placeholder',
                opacity: 0.7,
                update: function() {
                    CTMPackageBuilder.updateInclusionsJSON();
                }
            });
        },
        
        initFlatpickr: function() {
            $('.ctm-datepicker').flatpickr({
                dateFormat: 'Y-m-d',
                allowInput: true
            });
            
            $('.ctm-timepicker').flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true
            });
        },
        
        addDay: function(e) {
            e.preventDefault();
            
            var dayCount = $('.ctm-day-section').length + 1;
            var dayHtml = `
                <div class="ctm-day-section" data-day="${dayCount}">
                    <div class="ctm-day-header">
                        <h3 class="ctm-day-title">Day ${dayCount}</h3>
                        <div class="ctm-day-actions">
                            <button type="button" class="button button-small ctm-remove-day">
                                ${ctm_package.texts.remove_item}
                            </button>
                        </div>
                    </div>
                    <div class="ctm-day-content">
                        <div class="ctm-form-group">
                            <label for="day_${dayCount}_title">Day Title</label>
                            <input type="text" id="day_${dayCount}_title" 
                                   name="day_${dayCount}_title" 
                                   placeholder="e.g., Arrival & Welcome" 
                                   class="regular-text">
                        </div>
                        <div class="ctm-form-group">
                            <label for="day_${dayCount}_overview">Day Overview</label>
                            <textarea id="day_${dayCount}_overview" 
                                      name="day_${dayCount}_overview" 
                                      rows="3" 
                                      placeholder="Brief overview of the day..."></textarea>
                        </div>
                        <div class="ctm-activities-list">
                            <!-- Activities will be added here -->
                        </div>
                        <button type="button" class="button ctm-add-activity">
                            ${ctm_package.texts.add_item} Activity
                        </button>
                    </div>
                </div>
            `;
            
            $('#ctm-itinerary-days').append(dayHtml);
            this.updateItineraryJSON();
        },
        
        addActivity: function(e) {
            e.preventDefault();
            
            var $daySection = $(e.target).closest('.ctm-day-section');
            var dayNumber = $daySection.data('day');
            var activityCount = $daySection.find('.ctm-activity-item').length + 1;
            
            var activityHtml = `
                <div class="ctm-activity-item" data-activity="${activityCount}">
                    <div class="ctm-activity-header">
                        <div class="ctm-form-group" style="margin: 0; flex: 1;">
                            <input type="time" 
                                   name="day_${dayNumber}_activity_${activityCount}_time" 
                                   class="ctm-activity-time regular-text"
                                   placeholder="HH:MM">
                        </div>
                        <div class="ctm-activity-actions">
                            <button type="button" class="button button-small ctm-remove-activity">
                                ${ctm_package.texts.remove_item}
                            </button>
                        </div>
                    </div>
                    <div class="ctm-form-group">
                        <input type="text" 
                               name="day_${dayNumber}_activity_${activityCount}_title" 
                               placeholder="Activity title" 
                               class="regular-text">
                    </div>
                    <div class="ctm-form-group">
                        <textarea name="day_${dayNumber}_activity_${activityCount}_description" 
                                  rows="2" 
                                  placeholder="Activity description..."></textarea>
                    </div>
                    <div class="ctm-form-group">
                        <label>
                            <input type="checkbox" 
                                   name="day_${dayNumber}_activity_${activityCount}_meal" 
                                   value="1"> Includes Meal
                        </label>
                        <label style="margin-left: 15px;">
                            <input type="checkbox" 
                                   name="day_${dayNumber}_activity_${activityCount}_transport" 
                                   value="1"> Includes Transport
                        </label>
                    </div>
                </div>
            `;
            
            $daySection.find('.ctm-activities-list').append(activityHtml);
            this.updateItineraryJSON();
        },
        
        removeActivity: function(e) {
            e.preventDefault();
            $(e.target).closest('.ctm-activity-item').remove();
            this.updateItineraryJSON();
        },
        
        removeDay: function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to remove this day?')) {
                $(e.target).closest('.ctm-day-section').remove();
                this.renumberDays();
                this.updateItineraryJSON();
            }
        },
        
        renumberDays: function() {
            $('.ctm-day-section').each(function(index) {
                var dayNumber = index + 1;
                $(this).data('day', dayNumber);
                $(this).find('.ctm-day-title').text('Day ' + dayNumber);
                
                // Update all input names
                $(this).find('[name^="day_"]').each(function() {
                    var oldName = $(this).attr('name');
                    var newName = oldName.replace(/day_\d+_/, 'day_' + dayNumber + '_');
                    $(this).attr('name', newName);
                });
            });
        },
        
        addInclusion: function(e) {
            e.preventDefault();
            var type = $(e.target).data('type') || 'included';
            var listId = '#ctm-' + type + '-list';
            
            var itemCount = $(listId + ' .ctm-inclusion-item').length + 1;
            var itemId = type + '_' + itemCount;
            
            var inclusionHtml = `
                <div class="ctm-inclusion-item" data-id="${itemId}">
                    <span class="ctm-inclusion-type ${type}">
                        ${type.charAt(0).toUpperCase() + type.slice(1)}
                    </span>
                    <div class="ctm-form-group" style="flex: 1; margin: 0;">
                        <input type="text" 
                               name="${type}_item_${itemCount}" 
                               placeholder="Item description" 
                               class="regular-text">
                    </div>
                    <button type="button" class="button button-small ctm-remove-inclusion">
                        ${ctm_package.texts.remove_item}
                    </button>
                </div>
            `;
            
            $(listId).append(inclusionHtml);
            this.updateInclusionsJSON();
        },
        
        removeInclusion: function(e) {
            e.preventDefault();
            $(e.target).closest('.ctm-inclusion-item').remove();
            this.updateInclusionsJSON();
        },
        
        addSeasonalPrice: function(e) {
            e.preventDefault();
            var count = $('.ctm-seasonal-price-row').length + 1;
            
            var rowHtml = `
                <tr class="ctm-seasonal-price-row">
                    <td>
                        <input type="text" 
                               name="seasonal_start_${count}" 
                               class="ctm-datepicker regular-text" 
                               placeholder="YYYY-MM-DD">
                    </td>
                    <td>
                        <input type="text" 
                               name="seasonal_end_${count}" 
                               class="ctm-datepicker regular-text" 
                               placeholder="YYYY-MM-DD">
                    </td>
                    <td>
                        <input type="number" 
                               name="seasonal_price_${count}" 
                               class="regular-text" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00">
                    </td>
                    <td>
                        <input type="text" 
                               name="seasonal_note_${count}" 
                               class="regular-text" 
                               placeholder="Optional note">
                    </td>
                    <td>
                        <button type="button" class="button button-small ctm-remove-seasonal-price">
                            ${ctm_package.texts.remove_item}
                        </button>
                    </td>
                </tr>
            `;
            
            $('#ctm-seasonal-pricing tbody').append(rowHtml);
            this.initFlatpickr();
            this.updateSeasonalPricingJSON();
        },
        
        removeSeasonalPrice: function(e) {
            e.preventDefault();
            $(e.target).closest('.ctm-seasonal-price-row').remove();
            this.updateSeasonalPricingJSON();
        },
        
        updateItineraryJSON: function() {
            var itinerary = [];
            
            $('.ctm-day-section').each(function() {
                var dayNumber = $(this).data('day');
                var dayTitle = $(this).find('[name^="day_' + dayNumber + '_title"]').val();
                var dayOverview = $(this).find('[name^="day_' + dayNumber + '_overview"]').val();
                
                var day = {
                    day: dayNumber,
                    title: dayTitle,
                    overview: dayOverview,
                    activities: []
                };
                
                $(this).find('.ctm-activity-item').each(function() {
                    var activity = {
                        time: $(this).find('.ctm-activity-time').val(),
                        title: $(this).find('[name$="_title"]').val(),
                        description: $(this).find('[name$="_description"]').val(),
                        includes_meal: $(this).find('[name$="_meal"]').is(':checked') ? 1 : 0,
                        includes_transport: $(this).find('[name$="_transport"]').is(':checked') ? 1 : 0
                    };
                    
                    day.activities.push(activity);
                });
                
                itinerary.push(day);
            });
            
            $('#_itinerary').val(JSON.stringify(itinerary));
        },
        
        updateInclusionsJSON: function() {
            var inclusions = {
                included: [],
                excluded: [],
                addons: []
            };
            
            // Get included items
            $('#ctm-included-list .ctm-inclusion-item').each(function() {
                var item = $(this).find('input[type="text"]').val();
                if (item) {
                    inclusions.included.push(item);
                }
            });
            
            // Get excluded items
            $('#ctm-excluded-list .ctm-inclusion-item').each(function() {
                var item = $(this).find('input[type="text"]').val();
                if (item) {
                    inclusions.excluded.push(item);
                }
            });
            
            // Get addons (with prices)
            $('#ctm-addons-list .ctm-inclusion-item').each(function() {
                var item = $(this).find('input[type="text"]').val();
                var price = $(this).find('input[type="number"]').val();
                if (item) {
                    inclusions.addons.push({
                        name: item,
                        price: parseFloat(price) || 0
                    });
                }
            });
            
            $('#_inclusions').val(JSON.stringify(inclusions.included));
            $('#_exclusions').val(JSON.stringify(inclusions.excluded));
            $('#_addons').val(JSON.stringify(inclusions.addons));
        },
        
        updateSeasonalPricingJSON: function() {
            var seasonalPricing = [];
            
            $('.ctm-seasonal-price-row').each(function() {
                var start = $(this).find('[name^="seasonal_start_"]').val();
                var end = $(this).find('[name^="seasonal_end_"]').val();
                var price = $(this).find('[name^="seasonal_price_"]').val();
                var note = $(this).find('[name^="seasonal_note_"]').val();
                
                if (start && end && price) {
                    seasonalPricing.push({
                        start_date: start,
                        end_date: end,
                        price: parseFloat(price),
                        note: note
                    });
                }
            });
            
            $('#_seasonal_pricing').val(JSON.stringify(seasonalPricing));
        },
        
        loadSeasonalPricing: function() {
            var seasonalPricing = $('#_seasonal_pricing').val();
            if (seasonalPricing) {
                try {
                    var data = JSON.parse(seasonalPricing);
                    if (Array.isArray(data)) {
                        data.forEach(function(item, index) {
                            var count = index + 1;
                            var rowHtml = `
                                <tr class="ctm-seasonal-price-row">
                                    <td>
                                        <input type="text" 
                                               name="seasonal_start_${count}" 
                                               value="${item.start_date}" 
                                               class="ctm-datepicker regular-text">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="seasonal_end_${count}" 
                                               value="${item.end_date}" 
                                               class="ctm-datepicker regular-text">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="seasonal_price_${count}" 
                                               value="${item.price}" 
                                               class="regular-text" 
                                               step="0.01">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="seasonal_note_${count}" 
                                               value="${item.note || ''}" 
                                               class="regular-text">
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small ctm-remove-seasonal-price">
                                            ${ctm_package.texts.remove_item}
                                        </button>
                                    </td>
                                </tr>
                            `;
                            $('#ctm-seasonal-pricing tbody').append(rowHtml);
                        });
                        this.initFlatpickr();
                    }
                } catch (e) {
                    console.error('Error loading seasonal pricing:', e);
                }
            }
        },
        
        saveItinerary: function(e) {
            e.preventDefault();
            
            var $button = $(e.target);
            var originalText = $button.text();
            
            $button.text(ctm_package.texts.saving).prop('disabled', true);
            
            this.updateItineraryJSON();
            
            // Simulate save (in production, this would be AJAX)
            setTimeout(function() {
                $button.text(ctm_package.texts.saved);
                setTimeout(function() {
                    $button.text(originalText).prop('disabled', false);
                }, 2000);
            }, 1000);
        },
        
        duplicatePackage: function(e) {
            e.preventDefault();
            
            if (!confirm('Duplicate this package?')) {
                return;
            }
            
            var $button = $(e.target);
            $button.text('Duplicating...').prop('disabled', true);
            
            $.ajax({
                url: ctm_package.ajax_url,
                type: 'POST',
                data: {
                    action: 'ctm_duplicate_package',
                    package_id: ctm_package.post_id,
                    nonce: ctm_package.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.edit_url;
                    } else {
                        alert('Error: ' + response.data);
                        $button.text('Duplicate').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Network error');
                    $button.text('Duplicate').prop('disabled', false);
                }
            });
        },
        
        generatePackageCode: function(e) {
            e.preventDefault();
            
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            var code = 'CTM-';
            
            for (var i = 0; i < 6; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            $('#_package_code').val(code);
        },
        
        calculatePricePreview: function() {
            var basePrice = parseFloat($('#_base_price').val()) || 0;
            var participants = parseInt($('#_max_participants').val()) || 1;
            var total = basePrice * participants;
            
            $('.ctm-price-preview').text('$' + total.toFixed(2));
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        CTMPackageBuilder.init();
    });
    
})(jQuery);