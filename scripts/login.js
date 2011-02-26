
function handleLoginSubmit(eventObject){
	startWaitMessage();
	$.ajax({
		type: 'POST',
		url: "services/login",
		dataType: 'json',
		data: {login: $('#login').val(), password: $('#password').val()},
		success: handleLoginresult
	});
}

function handleLoginresult(data){
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else if (!data.user)
		setErrorMessage("Erreur de login.");
	else
		jumpTo('.');
	
	stopWaitMessage();
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
