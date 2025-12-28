<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ctm-schedule-meta">
    <h3><?php _e( 'Schedule Template', 'cst_system' ); ?></h3>
    <p class="description"><?php _e( 'Create a reusable schedule template for this package. Add schedule items below or load an example.', 'cst_system' ); ?></p>

    <div class="ctm-json-controls" style="margin-bottom: 15px;">
        <button type="button" class="button" id="ctm-load-schedule-example"><?php _e( 'Load Example Schedule', 'cst_system' ); ?></button>
    </div>

    <div id="ctm-schedule-items"></div>

    <p>
        <button type="button" class="button button-primary" id="ctm-add-schedule-item"><?php _e( 'Add Schedule Item', 'cst_system' ); ?></button>
    </p>

    <input type="hidden" name="_schedule_template" id="_schedule_template" value="<?php echo esc_attr( is_array( $schedule_template ) ? wp_json_encode( $schedule_template ) : $schedule_template ); ?>" />

    <table class="form-table" style="margin-top:12px">
        <tr>
            <th><label for="_start_time"><?php _e( 'Default Start Time', 'cst_system' ); ?></label></th>
            <td><input type="time" name="_start_time" id="_start_time" value="<?php echo esc_attr( $start_time ); ?>" /></td>
        </tr>
        <tr>
            <th><label for="_end_time"><?php _e( 'Default End Time', 'cst_system' ); ?></label></th>
            <td><input type="time" name="_end_time" id="_end_time" value="<?php echo esc_attr( $end_time ); ?>" /></td>
        </tr>
        <tr>
            <th><label for="_recurring_pattern"><?php _e( 'Recurring Pattern', 'cst_system' ); ?></label></th>
            <td>
                <select name="_recurring_pattern" id="_recurring_pattern">
                    <option value="" <?php selected( $recurring_pattern, '' ); ?>><?php _e( 'None', 'cst_system' ); ?></option>
                    <option value="daily" <?php selected( $recurring_pattern, 'daily' ); ?>><?php _e( 'Daily', 'cst_system' ); ?></option>
                    <option value="weekdays" <?php selected( $recurring_pattern, 'weekdays' ); ?>><?php _e( 'Weekdays', 'cst_system' ); ?></option>
                    <option value="weekends" <?php selected( $recurring_pattern, 'weekends' ); ?>><?php _e( 'Weekends', 'cst_system' ); ?></option>
                </select>
            </td>
        </tr>
    </table>

    <style>
    .ctm-schedule-row{border:1px solid #eee;padding:8px;margin-bottom:8px;border-left:4px solid #0073aa;background:#fff}
    .ctm-schedule-row .row-controls{float:right}
    .ctm-schedule-row input[type="time"]{width:120px}
    .ctm-schedule-row input[type="text"]{width:40%}
    .ctm-schedule-row textarea{width:98%;height:60px}
    </style>

    <script>
    (function(){
        const container = document.getElementById('ctm-schedule-items');
        const hidden = document.getElementById('_schedule_template');

        function createRow(item){
            const wrapper = document.createElement('div');
            wrapper.className = 'ctm-schedule-row';

            const controls = document.createElement('div');
            controls.className = 'row-controls';
            const remove = document.createElement('button');
            remove.type = 'button'; remove.className='button-link'; remove.textContent='Remove';
            remove.addEventListener('click', function(){ wrapper.remove(); });
            controls.appendChild(remove);

            const timeLabel = document.createElement('label');
            timeLabel.textContent = 'Time: ';
            const timeInput = document.createElement('input');
            timeInput.type='time'; timeInput.className='ctm-sch-time'; timeInput.value = item.time || '';
            timeLabel.appendChild(timeInput);

            const titleLabel = document.createElement('label');
            titleLabel.textContent = ' Title: ';
            const titleInput = document.createElement('input');
            titleInput.type='text'; titleInput.className='ctm-sch-title'; titleInput.value = item.title || '';
            titleLabel.appendChild(titleInput);

            const descLabel = document.createElement('label');
            descLabel.textContent = ' Description: ';
            const desc = document.createElement('textarea'); desc.className='ctm-sch-desc'; desc.textContent = item.description || '';

            wrapper.appendChild(controls);
            wrapper.appendChild(timeLabel);
            wrapper.appendChild(titleLabel);
            wrapper.appendChild(document.createElement('br'));
            wrapper.appendChild(desc);

            return wrapper;
        }

        function loadFromHidden(){
            let raw = hidden.value || '';
            let arr = [];
            try{
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) arr = parsed;
            } catch(e){ arr = []; }

            container.innerHTML = '';
            if (arr.length===0) container.appendChild(createRow({}));
            else arr.forEach(function(it){ container.appendChild(createRow(it)); });
        }

        function serializeToHidden(){
            const rows = container.querySelectorAll('.ctm-schedule-row');
            const out = [];
            rows.forEach(function(r){
                const time = r.querySelector('.ctm-sch-time') ? r.querySelector('.ctm-sch-time').value : '';
                const title = r.querySelector('.ctm-sch-title') ? r.querySelector('.ctm-sch-title').value : '';
                const desc = r.querySelector('.ctm-sch-desc') ? r.querySelector('.ctm-sch-desc').value : '';
                if (time || title || desc) out.push({time:time,title:title,description:desc});
            });
            hidden.value = JSON.stringify(out);
        }

        document.getElementById('ctm-add-schedule-item').addEventListener('click', function(e){ e.preventDefault(); container.appendChild(createRow({})); });

        // expose helper for external pages
        window.ctmSchedule = window.ctmSchedule || {};
        window.ctmSchedule.loadFromString = function(str){ hidden.value = str || ''; loadFromHidden(); };
        window.ctmSchedule.serializeToHidden = serializeToHidden;

        // serialize before post submit
        const postForm = document.querySelector('#post');
        if (postForm) postForm.addEventListener('submit', function(){ serializeToHidden(); });

        // initial
        loadFromHidden();
    })();
    </script>

</div>
