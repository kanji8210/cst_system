(function($){
    // Simple itinerary manager: add days, add activities, drag & drop
    function serializeItinerary() {
        var days = [];
        $('.ctm-day').each(function(){
            var $day = $(this);
            var dayNumber = $day.data('day') || 1;
            var activities = [];
            $day.find('.ctm-activity').each(function(){
                var $act = $(this);
                activities.push({
                    time: $act.find('.act-time').val() || '',
                    title: $act.find('.act-title').val() || '',
                    desc: $act.find('.act-desc').val() || '',
                    location: $act.find('.act-location').val() || '',
                    included: $act.find('.act-included').val() || ''
                });
            });
            days.push({ day: dayNumber, activities: activities });
        });
        return days;
    }

    function ensureSortable() {
        $('.ctm-activities').each(function(){
            if (!this._sortable) {
                this._sortable = Sortable.create(this, { animation: 150, handle: '.act-handle' });
            }
        });
        if (!window._daySortable) {
            window._daySortable = Sortable.create(document.getElementById('ctm-days'), { animation: 150, handle: '.day-handle' });
        }
    }

    $(document).ready(function(){
        // Add day
        $('#ctm-add-day').on('click', function(e){
            e.preventDefault();
            var next = $('.ctm-day').length + 1;
            var html = $('#ctm-day-template').html();
            var $node = $(html.replace(/__DAY__/g, next));
            $('#ctm-days').append($node);
            ensureSortable();
        });

        // Delegate add activity
        $(document).on('click', '.ctm-add-activity', function(e){
            e.preventDefault();
            var $day = $(this).closest('.ctm-day');
            var html = $('#ctm-activity-template').html();
            var $node = $(html);
            $day.find('.ctm-activities').append($node);
            ensureSortable();
        });

        // Delegate remove activity
        $(document).on('click', '.ctm-remove-activity', function(e){
            e.preventDefault();
            $(this).closest('.ctm-activity').remove();
        });

        // Before submit, serialize into hidden field
        $('form').on('submit', function(){
            var it = serializeItinerary();
            $('#ctm_itinerary_input').val( JSON.stringify(it) );
        });

        // init existing sortable
        ensureSortable();
    });

})(jQuery);
