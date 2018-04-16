/* SMS Commande - Send SMS since FICHINTER COMMANDE card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

$('.smsSendBtn').on('click', function(){
    
    var id = $(this).attr('data-id');
    
    commandeId = id;
    
    var sendBtn = $( "#dialog-confirm-"+id ).data('btnSendTxt');
    var cancelBtn = $( "#dialog-confirm-"+id ).data('btnCancelTxt');
    
    
    
    $( "#dialog-confirm-"+id ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: 
      [{
        text: $( "#dialog-confirm-"+id).attr('data-btnSendTxt'),
        "id": "btnSendSmsOk",
        click: function () {
            $( "#btnSendSmsOk" ).button({
                disabled: true
            });
            ajaxSendSms();
        },

    }, {
        text: $( "#dialog-confirm-"+id ).attr('data-btnCancelTxt'),
        click: function () {
            $( this ).dialog( "close" );
        },
    }],
    });
});

function ajaxSendSms() {
    if (typeof commandeId != 'undefined') {
        $.ajax({
            type: "POST",
            url: ajaxFile,
            data: {id: commandeId},
            dataType: 'html'
        }).done(function( data ) {
            location.reload();
        });
    }
}

