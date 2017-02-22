jQuery(document).bind("omeka:elementformload", function () {
    jQuery("input[data-type='isuppression']").each(function () {
        var $ = jQuery;
        var base = $(this);
        var description_id = '#' + jQuery(this).data('description');
        var description_reason_id = '#' + jQuery(this).data('description-reason');
        function show(id) {
            $(id).parent().addClass('dependent-display').removeClass('dependent-hidden');
        }

        function hide(id) {
            $(id).parent().addClass('dependent-hidden').removeClass('dependent-display');
        }

        function updateDependentCheckbox() {
            if ($(description_id).prop('checked')) {
                show(description_reason_id);
            }
            else {
                hide(description_reason_id);
            }

            base.val(JSON.stringify({
                "description": $(description_id).prop('checked'),
                "description-reason": $(description_reason_id).val()
            }));
        }

        updateDependentCheckbox();
        $(description_id).change(function () {
            updateDependentCheckbox();
        });
        $(description_reason_id).change(function () {
            updateDependentCheckbox();
        });
    });
    jQuery("input[data-type='suppression']").each(function () {
        var $ = jQuery;
        var base = $(this);
        var recursive_id = '#' + jQuery(this).data('recursive');
        var recursive_reason_id = '#' + jQuery(this).data('recursive-reason');
        var description_id = '#' + jQuery(this).data('description');
        var description_reason_id = '#' + jQuery(this).data('description-reason');
        function show(id) {
            $(id).parent().addClass('dependent-display').removeClass('dependent-hidden');
        }

        function hide(id) {
            $(id).parent().addClass('dependent-hidden').removeClass('dependent-display');
        }

        function updateDependentCheckbox() {
            if ($(recursive_id).prop('checked')) {
                show(recursive_reason_id);
                show(description_id);
            }
            else {
                hide(recursive_reason_id);
                hide(description_id);
            }

            if ($(description_id).prop('checked')) {
                show(description_reason_id);
            }
            else {
                hide(description_reason_id);
            }

            base.val(JSON.stringify({
                "recursive": $(recursive_id).prop('checked'),
                "recursive-reason": $(recursive_reason_id).val(),
                "description": $(description_id).prop('checked'),
                "description-reason": $(description_reason_id).val()
            }));
        }

        updateDependentCheckbox();
        $(recursive_id).change(function () {
            updateDependentCheckbox();
        });
        $(recursive_reason_id).change(function () {
            updateDependentCheckbox();
        });
        $(description_id).change(function () {
            updateDependentCheckbox();
        });
        $(description_reason_id).change(function () {
            updateDependentCheckbox();
        });
    });
});
