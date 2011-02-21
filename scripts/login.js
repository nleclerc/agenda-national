
function handleLoginSubmit(eventObject){
	$.ajax({
		type: 'POST',
		url: "services/login.php",
		dataType: 'json',
		data: {login: $('#login').val(), password: $('#password').val()},
		success: handleLoginresult
	});
}

function handleLoginresult(data){
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else if (!data.loggedIn)
		setErrorMessage("Erreur de login.");
	else
		jumpTo('.');
}

function applyPlaceholderWorkaround(){
	$('input').each(function(index, item){
		item = $(item);
		
		var placeholder = item.attr('placeholder');
		if (placeholder) {
			item.addClass('placeholderWorkaround');
			item.val(placeholder);
			item.focus(function(){
				if (item.val() == placeholder){
					item.val('');
					item.removeClass('placeholderWorkaround');
				}
			});
			
			item.blur(function(){
				if (!item.val()){
					item.addClass('placeholderWorkaround');
					item.val(placeholder);
				}
			})
		}
	});
}
