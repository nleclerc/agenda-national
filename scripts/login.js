
function handleLoginSubmit(eventObject){
	$.getJSON("services/login.php", {login: $('#login').val(), password: $('#password').val()}, handleLoginresult);
}

function handleLoginresult(data){
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else if (!data.loggedIn)
		setErrorMessage("Erreur de login.");
	else
		setLoggedIn(data.username);
}

function showLoginForm() {
	$('#mainContent').html('');
	$('#authenticationZone').append(
		$('<form name="loginForm" id="loginForm" target="dummyFrame" action="dummy.txt" method="post">'+
		'<input type="text" id="login" name="login" placeholder="identifiant">'+
		'<input type="password" name="password" id="password" placeholder="mot de passe">'+
		'<input type="submit" value="Valider">'+
		'</form>').submit(handleLoginSubmit)
	);
	
	if (!Modernizr.input.placeholder) {
		// TODO: handle case without placeholder support.
	}
}
