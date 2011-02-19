
function handleLoginSubmit(eventObject){
	$.getJSON("services/login.php", {login: $('#login').val(), password: $('#password').val()}, handleLoginresult);
}

function handleLoginresult(data){
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else if (!data.loggedIn)
		setErrorMessage("Erreur de login.");
	else
		jumpTo('.');
}
