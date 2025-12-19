// File: admin/js/database-widget.js
(function($) {
    'use strict';
    
    $(document).ready(function() {
        var $widget = $('.ctm-database-widget');
        var $messages = $('#ctm-db-messages');
        var $progress = $('#ctm-db-progress');
        
        // Show message helper
        function showMessage(text, type) {
            $messages
                .removeClass('success error')
                .addClass(type)
                .text(text)
                .show();
            
            setTimeout(function() {
                $messages.fadeOut();
            }, 5000);
        }
        
        // Update progress bar
        function updateProgress(percent, text) {
            $progress.find('.ctm-progress-fill').css('width', percent + '%');
            $progress.find('.ctm-progress-text').text(text || percent + '%');
            $progress.show();
        }
        
        // Hide progress bar
        function hideProgress() {
            setTimeout(function() {
                $progress.fadeOut();
                setTimeout(function() {
                    $progress.find('.ctm-progress-fill').css('width', '0%');
                    $progress.find('.ctm-progress-text').text('0%');
                }, 300);
            }, 500);
        }
        
        // Create single table
        $widget.on('click', '.ctm-create-table', function(e) {
            e.preventDefault();
            var $button = $(this);
            var tableKey = $button.data('table');
            
            $button.prop('disabled', true).text(ctm_db_widget.texts.creating);
            updateProgress(30, ctm_db_widget.texts.creating);
            
            $.ajax({
                url: ctm_db_widget.ajax_url,
                type: 'POST',
                data: {
                    action: 'ctm_manage_database',
                    action_type: 'create_table',
                    table_key: tableKey,
                    nonce: ctm_db_widget.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(ctm_db_widget.texts.success, 'success');
                        // Reload widget content after 1 second
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showMessage(ctm_db_widget.texts.error + ' ' + response.data, 'error');
                        $button.prop('disabled', false).text('Create');
                    }
                    hideProgress();
                },
                error: function() {
                    showMessage(ctm_db_widget.texts.error + ' Network error', 'error');
                    $button.prop('disabled', false).text('Create');
                    hideProgress();
                }
            });
        });
        
        // Create all missing tables
        $widget.on('click', '.ctm-create-missing-tables', function() {
            var $button = $(this);
            var missingTables = [];
            
            // Find all missing tables
            $('.ctm-create-table').each(function() {
                missingTables.push($(this).data('table'));
            });
            
            if (missingTables.length === 0) {
                showMessage('No missing tables to create', 'success');
                return;
            }
            
            $button.prop('disabled', true);
            updateProgress(10, 'Starting table creation...');
            
            $.ajax({
                url: ctm_db_widget.ajax_url,
                type: 'POST',
                data: {
                    action: 'ctm_manage_database',
                    action_type: 'create_missing',
                    nonce: ctm_db_widget.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateProgress(100, 'Tables created successfully!');
                        showMessage('All missing tables have been created', 'success');
                        
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage(ctm_db_widget.texts.error + ' ' + response.data, 'error');
                        $button.prop('disabled', false);
                    }
                    hideProgress();
                },
                error: function() {
                    showMessage(ctm_db_widget.texts.error + ' Network error', 'error');
                    $button.prop('disabled', false);
                    hideProgress();
                }
            });
        });
        
        // Repair table
        $widget.on('click', '.ctm-repair-table', function() {
            var $button = $(this);
            var tableName = $