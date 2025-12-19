<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Schedule Builder admin page
$packages = get_posts( array( 'post_type' => 'ctm_package', 'posts_per_page' => -1 ) );
?>
<div class="wrap">
    <h1><?php _e( 'Schedule Builder', 'cst_system' ); ?></h1>

    <p><?php _e( 'Select a package to edit its schedule template visually.', 'cst_system' ); ?></p>

    <p>
        <label for="ctm-sel-package"><?php _e( 'Package:', 'cst_system' ); ?></label>
        <select id="ctm-sel-package">
            <option value="">— <?php _e( 'Select package', 'cst_system' ); ?> —</option>
            <?php foreach ( $packages as $p ): ?>
                <option value="<?php echo esc_attr( $p->ID ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>

    <div id="ctm-schedule-editor-area" style="display:none">
        <form id="ctm-schedule-form">
            <?php wp_nonce_field( 'ctm_save_schedule', 'ctm_save_schedule_nonce' ); ?>
            <input type="hidden" id="ctm-schedule-post-id" name="post_id" value="" />
            <input type="hidden" id="ctm-schedule-json" name="schedule" value="" />

            <div id="ctm-schedule-items"></div>

            <p>
                <button type="button" class="button" id="ctm-add-schedule-item">Add Item</button>
                <button type="button" class="button button-primary" id="ctm-save-schedule">Save Schedule</button>
            </p>
        </form>
    </div>

    <style>
    .ctm-schedule-row{border:1px solid #eee;padding:8px;margin-bottom:8px;border-left:4px solid #0073aa;background:#fff}
    .ctm-schedule-row .row-controls{float:right}
    .ctm-schedule-row input[type="time"]{width:120px}
    .ctm-schedule-row input[type="text"]{width:40%}
    .ctm-schedule-row textarea{width:98%;height:60px}
    </style>

    <script>
    (function(){
        const sel = document.getElementById('ctm-sel-package');
        const area = document.getElementById('ctm-schedule-editor-area');
        const items = document.getElementById('ctm-schedule-items');
        const hidden = document.getElementById('ctm-schedule-json');
        const postIdInput = document.getElementById('ctm-schedule-post-id');

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

        function loadSchedule(raw){
            let arr = [];
            if (!raw) raw = '';
            try{ arr = JSON.parse(raw); } catch(e){ arr = []; }
            items.innerHTML = '';
            if (arr.length===0) items.appendChild(createRow({}));
            else arr.forEach(function(it){ items.appendChild(createRow(it)); });
        }

        function serialize(){
            const rows = items.querySelectorAll('.ctm-schedule-row');
            const out = [];
            rows.forEach(function(r){
                const time = r.querySelector('.ctm-sch-time') ? r.querySelector('.ctm-sch-time').value : '';
                const title = r.querySelector('.ctm-sch-title') ? r.querySelector('.ctm-sch-title').value : '';
                const desc = r.querySelector('.ctm-sch-desc') ? r.querySelector('.ctm-sch-desc').value : '';
                if (time || title || desc) out.push({time:time,title:title,description:desc});
            });
            hidden.value = JSON.stringify(out);
            return hidden.value;
        }

        sel.addEventListener('change', function(){
            const pid = parseInt(this.value) || 0;
            if (!pid) { area.style.display='none'; return; }
            // fetch schedule
            fetch( ajaxurl + '?action=ctm_get_schedule&post_id=' + pid, {credentials:'same-origin'} ).then(r=>r.json()).then(function(r){
                if (r.success) {
                    loadSchedule( r.data.schedule );
                    postIdInput.value = pid;
                    area.style.display = 'block';
                } else {
                    alert('Error: ' + r.data);
                }
            });
        });

        document.getElementById('ctm-add-schedule-item').addEventListener('click', function(e){ e.preventDefault(); items.appendChild(createRow({})); });

        document.getElementById('ctm-save-schedule').addEventListener('click', function(e){
            e.preventDefault();
            const pid = parseInt(postIdInput.value) || 0;
            if (!pid) { alert('No package selected'); return; }
            const payload = new FormData();
            payload.append('action','ctm_save_schedule');
            payload.append('post_id', pid);
            payload.append('schedule', serialize());
            payload.append('ctm_save_schedule_nonce', document.querySelector('#ctm-schedule-form input[name="ctm_save_schedule_nonce"]').value );

            fetch( ajaxurl, { method:'POST', credentials:'same-origin', body: payload } ).then(r=>r.json()).then(function(r){
                if (r.success) {
                    alert('Saved');
                } else {
                    alert('Error: ' + (r.data||'unknown'));
                }
            });
        });

    })();
    </script>
</div>
