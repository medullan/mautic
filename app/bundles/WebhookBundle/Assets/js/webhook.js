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
    if (mQuery(el).val() === "1") {
        mQuery('#campaignevent_properties_additional_data_raw').addClass('hide');
        mQuery('#campaignevent_properties_additional_data_list').addClass('hide');
        mQuery('#campaignevent_properties_additional_data_list').prop('checked',false);

        mQuery('#campaignevent_properties_additional_data_raw').removeClass('hide');
        mQuery('label[for=campaignevent_properties_additional_data_raw]').removeClass('hide');
        mQuery('#campaignevent_properties_additional_data_raw').prop('checked',true);
        
    }else{        
        mQuery('#campaignevent_properties_additional_data_raw').addClass('hide');
        mQuery('label[for=campaignevent_properties_additional_data_raw]').addClass('hide');
        mQuery('#campaignevent_properties_additional_data_raw').prop('checked',false);

        mQuery('#campaignevent_properties_additional_data_list').removeClass('hide');        
        mQuery('#campaignevent_properties_additional_data_list').prop('checked',true);
    }
}

mQuery( document ).ajaxStop(function() {    
    if(mQuery('#campaignevent_properties_dataType_1').prop('checked') === true){
        mQuery('#campaignevent_properties_additional_data_list').addClass('hide');
        mQuery('#campaignevent_properties_additional_data_raw').removeClass('hide');
    }
});