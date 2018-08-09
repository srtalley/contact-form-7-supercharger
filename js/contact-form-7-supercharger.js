//1.0.2.1

jQuery(function($) {


$(document).ready(function() {

  //add class on input focus
  $('.wpcf7-form-control').focus(function(){
    $(this).closest('.ds-hover-label').addClass('ds-hover-label-focus');
  }).blur(function(){
    $(this).closest('.ds-hover-label').removeClass('ds-hover-label-focus');
  });

  //hide or show the label if the field is full or empty
  $('.wpcf7-form-control').blur(function(){
    if($(this).val() == '') {
      $(this).closest('.ds-hover-label').removeClass('ds-hover-label-hide');
    } else {
      $(this).closest('.ds-hover-label').addClass('ds-hover-label-hide');
    }
  });

}); //end $(document).ready(function()

//Events to listen to
document.addEventListener('wpcf7invalid', function (event){

  showDSModal(event.target);
  //put input in the first invalid item
  $('#' + event.target.id + ' .wpcf7-not-valid:first').focus();

}); //end $(document).on('invalid.wpcf7', function ()

//Events to listen to
document.addEventListener('wpcf7mailsent', function (event){

  //Remove the hover labels
  $(event.target).find('.ds-hover-label').removeClass('ds-hover-label-hide');
  //Show the modal
  showDSModal(event.target, 'success');

  //check the inputs and see if there was a hidden redirect input
  var inputs = event.detail.inputs;

  inputs.forEach(function(input){
    if(input.name == 'redirectpage'){
      //check if a valid URL
      var is_valid_url = validURL(input.value);

      if(is_valid_url) {
        //add to the lightbox
        var form_id = event.detail.id;
        var form_div = document.getElementById(form_id);
        var form_modal = form_div.getElementsByClassName('ewpcf7-modal-additional-messages');
        var redirect_element = document.createElement("h4")
        redirect_element.className = "ewpcf7-modal-redirecting";
        redirect_element.innerHTML = "Please wait. Redirecting...";
        form_modal[0].appendChild(redirect_element);
        form_modal[0].className += (' ewpcf7-modal-show-additional');
        //go to the redirect
        location = input.value;
      } else {
        console.log('ERROR: Invalid RedirectPage URL was entered in Contact Form 7 Settings for this form.');
      } //end if valid
    } //end if input.name redirectpage
  }); //end inputs.forEach

});

//Remove the loading class
document.addEventListener('wpcf7submit', function (event){
  $('.wpcf7-loading').removeClass('wpcf7-loading');
});
//Add the loading class
$('.wpcf7').submit(function(){
  // $(this).addClass('wpcf7-loading');
  $('body').addClass('wpcf7-loading');

});
//Remove model on close or clicking outside the box
$('body').on('click', '.ewpcf7-modal-background',function(){
  hideDSModal(this);
});

$('body').on('click', '.ewpcf7-close',function(){
  hideDSModal(this);
});


function showDSModal(modal_object, wpcf7_modal_icon){

  var modal_type;
  if(wpcf7_modal_icon == 'notice' || wpcf7_modal_icon == null) {
    modal_type = 'ewpcf7-modal-notice';
  } else if(wpcf7_modal_icon == 'success'){
    modal_type = 'ewpcf7-modal-success';
  }
  $(modal_object).find('.ewpcf7-modal').addClass('ewpcf7-modal-show-bg '  + modal_type);
  //have to set delay before adding the next class so that it fades in
  setTimeout(function(){
    $(modal_object).find('.ewpcf7-modal').addClass('ewpcf7-modal-show-message');
  }, 100);
} // end function showDSModal

function hideDSModal(modal_object){
  //Get the form name
  var ds_ewpcf7_current_form = $(modal_object).parentsUntil('.wpcf7-form').parent();

  $(modal_object).closest('.ewpcf7-modal').removeClass('ewpcf7-modal-show-bg ewpcf7-modal-show-message ewpcf7-modal-notice ewpcf7-modal-success ');

  if($(ds_ewpcf7_current_form).hasClass('invalid')) {
    //put input in the first invalid item if it exists
    $(ds_ewpcf7_current_form).find('.wpcf7-not-valid:first').focus();
  }
} // end function hideDSModal

function validURL(str) {
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name and extension
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?'+ // port
  '(\\/[-a-z\\d%@_.~+&:]*)*'+ // path
  '(\\?[;&a-z\\d%@_.,~+&:=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  if(!pattern.test(str)) {
    return false;
  } else {
    return true;
  }
}

});
