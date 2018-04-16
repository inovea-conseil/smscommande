$(function(){
    $('textarea').on('change keyup paste', function(){
        $('.smsHelp[data-textarea='+$(this).attr('name')+']').find('.nbCharSMS').html($(this).val().length);
        $('.smsHelp[data-textarea='+$(this).attr('name')+']').find('.nbSMS').html(Math.ceil($(this).val().length/160));
    });
});
