Mautic.sendHookTest = function() {

    var url = mQuery('#webhook_webhookUrl').val();
    var eventTypes = mQuery("#event-types input[type='checkbox']");
    var selectedTypes = [];

    eventTypes.each(function() {
        var item = mQuery(this);
        if (item.is(':checked')) {
            selectedTypes.push(item.val());
        }
    });

    var data = {
        action: 'webhook:sendHookTest',
        url: url,
        types: selectedTypes
    };

    var spinner = mQuery('#spinner');

    // show the spinner
    spinner.removeClass('hide');

    mQuery.ajax({
        url: mauticAjaxUrl,
        data: data,
        type: 'POST',
        dataType: "json",
        success: function(response) {
            if (response.success) {
                mQuery('#tester').html(response.html);
            }
        },
        error: function (request, textStatus, errorThrown) {
            Mautic.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function(response) {
            spinner.addClass('hide');
        }
    })
};

/**
 * Show the correct form to submit the data
 */
Mautic.webhookToggleTypes = function(el) {
    const additionalDataRawSelector = '#campaignevent_properties_additional_data_raw';
    const additionalDataListSelector = '#campaignevent_properties_additional_data_list';
    const additionalDataRawLabelSelector = 'label[for=campaignevent_properties_additional_data_raw]';

    if (mQuery(el).val() === "1") {
        // raw additional data
        mQuery(additionalDataListSelector).addClass('hide');
        mQuery(additionalDataRawSelector).removeClass('hide');
        mQuery(additionalDataRawLabelSelector).removeClass('hide');

        mQuery(additionalDataListSelector).prop('checked',false);
        mQuery(additionalDataRawSelector).prop('checked',true);
    } else {
        mQuery(additionalDataRawSelector).addClass('hide');
        mQuery(additionalDataRawLabelSelector).addClass('hide');
        mQuery(additionalDataListSelector).removeClass('hide');

        mQuery(additionalDataRawSelector).prop('checked',false);
        mQuery(additionalDataListSelector).prop('checked',true);
    }
};
