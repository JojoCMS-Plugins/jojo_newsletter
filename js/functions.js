$(document).ready(function(){
  /* client side validation for newsletter form */
  $('div.subscribe_sidebar form').submit(function(){
    var id = $(this).parent().attr('id').replace(/subscribe_sidebar_(\d)+/g, "$1"); //get the numberic ID from the DOM ID
    var error = '';
    if ($('#subscribe_firstname_'+id).val()=='') {error = error+"Please enter your first name.\n"}
    if ($('#subscribe_email_'+id).val()=='') {error = error+"Please enter your email address.\n"}
    if (error != '') {
        alert(error);
        return false;
    } else {
        return true;
    }
  });
});