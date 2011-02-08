function loadMember(){
	$.getJSON("services/getMemberData.php"+window.location.search, null, handleData);
}

function handleData(data){
	if (isDefined(data.loggedIn) && !data.loggedIn) {
		window.location.href = "login.html";
		return;
	}
	
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	
	if (data.id) {
		setLoggedIn(data.username);
		
		$('#memberName').html(data.name);
		document.title = data.name+' ['+document.title+']';
		
		$('#memberId').html('#'+data.id);
		$('#memberRegion').html(' - '+data.region);
		
		if (data.motto)
			$('#memberMotto').html('"'+data.motto+'"').show();
		
		processActions(data);
		processLanguages(data.languages);
		processInterests(data.interests);
		
		$('#pageBody').fadeIn(200);
	}
}

function sortInterests(interests){
	
	function sortLevel(interests){
		const levels = ['Passionné','Intéressé','Curieux'];
		levels.reverse();
		
		const source = interests.concat();
		var result = new Array();
		
		for (var v=0; v<levels.length; v++){
			var currentLevel = levels[v];
			
			for (var i=source.length-1; i>=0; i--){ 
				// iterate down because of items removed in array
				
				if (source[i].level == currentLevel)
					result.unshift(source.splice(i, 1)[0]);
			}
		}
		
		// add interests with skills not in list.
		result = result.concat(source);
		
		return result;
	}
	
	const skills = ['Professionnel','Expert','Éclairé','Débutant'];
	skills.reverse();
	
	const source = interests.concat();
	var result = new Array();
	
	for (var s=0; s<skills.length; s++){
		var currentItems = new Array();
		var currentSkill = skills[s];
		
		for (var i=source.length-1; i>=0; i--){ 
			// iterate down because of items removed in array
			
			if (source[i].skill == currentSkill)
				currentItems.unshift(source.splice(i, 1)[0]);
		}
		
		result = sortLevel(currentItems).concat(result);
	}
	
	// add interests with skills not in list.
	result = result.concat(source);
	
	return result;
}

function processInterests(interests){
	if (!interests)
		return;
	
	interests = sortInterests(interests);
	
	var hasInterests = false;
	var list = $('#interestList');
	
	for (var i=0; i<interests.length; i++){
		var interest = interests[i];
		
		var details = '';
		
		if (interest.skill)
			details += interest.skill;
		
		if (interest.level) {
			if (details)
				details += ' ';
			
			details += interest.level;
		}
		
		list.append(createListItem(interest.name, details).setFirst(!hasInterests).highlight(isInterestReference(interest.skill)));
		hasInterests = true;
	}
	
	if (hasInterests)
		$('#interestBlock').show();
}

function isInterestReference(skill){
	return skill == 'Professionnel' || skill == 'Expert';
}

function processLanguages(languages){
	if (!languages)
		return;
	
	var hasLanguage = false;
	var list = $('#languageList');
	
	for (var i=0; i<languages.length; i++){
		var l = languages[i];
		list.append(createListItem(l.name, l.level, null, null, hasLanguage, isFluent(l.level)).setSingleLine());
		hasLanguage = true;
	}
	
	if (hasLanguage)
		$('#languageBlock').show();
}

function isFluent(languageLevel){
	return languageLevel == 'Maternelle' || languageLevel == 'Courant' || languageLevel == 'Expert'; 
};

function processActions(data){
	var actionList = $('#actionList');
	var hasAction = false;
	
	var actions = sortActions(data.contacts)
	
	for (var i=0; i<actions.length; i++) {
		actionList.append(createAction(actions[i].type, actions[i].value, hasAction));
		hasAction = true;
	}
	
	if (data.address) {
		actionList.append(createAction('address', data.address.replace(/\n/g,' '), hasAction));
		hasAction = true;
	}
	
	if (hasAction)
		$('#actionBlock').show();
}

function sortActions(actionList){
	if (!actionList)
		return new Array();
	
	function listTypes(actions, typeName){
		var actionsOfType = new Array();
		
		for (var i=actions.length-1; i>=0; i--)
			if (actions[i].type == typeName){
				actionsOfType.unshift(actions[i]);
				actions.splice(i,1);
			}
		
		return actionsOfType;
	}
	
	var typeOrder = ['mobile', 'phone', 'workphone', 'email', 'website'];
	var actions = actionList.concat();
	var result = new Array();
	
	$.each(typeOrder, function(index, type){
		result = result.concat(listTypes(actions,type));
	});
	
	result = result.concat(actions); // add remaining actions
	return result;
}

function createAction(type, value, isSubseq){
	var message = 'Contacter';
	var link = null;
	
	if (type == "email") {
		message = "Envoyer un email";
		link = 'mailto:'+value;
	} else if (type == "phone") {
		message = "Appeler sur son fixe";
		link = 'tel:'+value;
	} else if (type == "workphone") {
		message = "Appeler sur son lieu de travail";
		link = 'tel:'+value;
	} else if (type == "mobile") {
		message = "Appeler sur son mobile";
		link = 'tel:'+value;
	} else if (type == "address") {
		message = "Localiser";
		link = 'http://maps.google.fr/maps?q='+value;
	} else if (type == "website") {
		message = "Visiter son site";
		if (value.indexOf('://') >= 0)
			link = value;
		else
			link = 'http://'+value;
	}
	
	return createListItem(message, value, 'action-'+type, link, isSubseq);
}
