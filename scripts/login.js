
function setContentBody (html){
	$('#contentBody').hide().html(html).fadeIn(200);
}

function showLoginForm(){
	
	var body = '';
	
	// The iframe and form action are used to allow browsers to save login information.
	body += '<iframe name="dummyFrame" class="hidden" src="dummy.txt"></iframe>';
	body += '<form name="loginForm" id="loginForm" target="dummyFrame" action="dummy.txt">';
	body += '<div id="errorMessage"></div>';
	body += '<div class="inputLabel">Identifiant</div>';
	body += '<input type="text" id="login" name="login">';

	body += '<div class="inputLabel">Mot de passe</div>';
	body += '<input type="password" id="pwd" name="pwd">';

	body += '<input id="loginSubmit" type="submit" value="Valider">';

	body += '<a class="footerLink" href="mailto:nl@spirotron.fr?subject=[iAgenda] Remarque">Contact</a>';
	body += '<a class="footerLink" href="https://github.com/nleclerc/iagenda-mobile">Code Source</a>';
	body += '<a class="footerLink" id="qrcodelink" href="#" onclick="toggleQRCode();return false;">QRCode</a>';

	body += '<div id="qrcode"></div>';
	body += '</form>';
		
	setContentBody(body);
	
	$('#loginForm').submit(handleLoginSubmit);
}

function handleLoginSubmit(eventObject){
	$.getJSON("services/login.php", {login: $('#login').val(), pwd: $('#pwd').val()}, handleLoginresult);
}

function handleLoginresult(data){
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else if (!data.loggedIn)
		setErrorMessage("Erreur de login.");
	else {
//		$('#headerTitle').html(data.username);
		jumpTo(".");
	}
}

function toggleQRCode(){
	var code = $('#qrcode');
	if (code.html() == '')
		code.html('<img class="qrcode" src="http://chart.apis.google.com/chart?cht=qr&chs=150x150&choe=UTF-8&chld=chld=L|1&chl='+window.location.href+'">');
	else
		code.html('');
}
