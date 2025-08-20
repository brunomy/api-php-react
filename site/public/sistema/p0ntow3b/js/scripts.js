
$(document).ready(function(){

	$('.form_login input[name=password]').keypress();

    $(".form_login").clickform({'validateUrl': 'scripts/form-login.php', 'submitButton': '.form_actions__bt_submit'}, function (data) {
        if(data.type == 'success'){
        	//$('.form_ponto input[name=senha]').val($('.form_login input[name=password]').val())
        	$('.form_ponto input[name=senha]').val(data.senha);
        	$('.form_ponto .form_actions__bt_submit').trigger('click');
        }
    });

    $(".form_ponto").clickform({'validateUrl': '../scripts/form-ponto.php', 'submitButton': '.form_actions__bt_submit'}, function (data) {
        $('.form_login input[name=user_key]').val("");
    });

});