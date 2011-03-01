function loadMember(hash){
	if (hash && hash.match(/^#\d+$/))
		callService("getMemberData", {memberId:hash.substr(1)}, handleMemberData);
	else
		setErrorMessage('Membre non spécifié.');
}

function handleMemberData(data){
	document.title = data.name+' [Agenda Mensa]';
	
	var table = $('<table>').attr({id:'memberTable'});
	
	var header = $('<th>').append(data.name+' ').append(
		$('<span>').addClass('subtitle').text(data.region_id+' #'+data.id)
	).appendTo($('<tr>').appendTo(table));
	insertBackButton(header);
	
	if (data.motto) {
		createItemRow('Devise', applyHtmlLineBreaks(data.motto)).appendTo(table);
	}
	
	if (data.contacts && data.contacts.length > 0) {
		createRow('Contacter', 'header').appendTo(table);
		
		$(sortActions(data.contacts)).each(function(index, item){
			createAction(item.type, item.value).appendTo(table);
		});
	}
	
	if (data.languages && data.languages.length > 0) {
		createRow('Langues', 'header').appendTo(table);
		
		$(data.languages).each(function(index, item){
			createItemRow(item.name, item.level, null, isFluent(item.level)).appendTo(table);
		});
	}
	
	if (data.interests && data.interests.length > 0) {
		createRow('Intérêts', 'header').appendTo(table);
		
		$(sortInterests(data.interests)).each(function(index, item){
			var details = new Array();
			
			if (item.skill)
				details.push(item.skill);
			if (item.level)
				details.push(item.level);
			
			createItemRow(item.name, details.join(' '), null, isInterestReference(item.skill)).appendTo(table);
		});
	}
	
	setMainContent(table);
}

function createRow(content, classname){
	var row = $('<tr>');
	var cell = $('<td>').append(content).appendTo(row);
	if (classname)
		cell.addClass(classname);
	
	return row;
}

function createItemRow(label, details, url, highlight){
	var link = $('<a>').addClass('memberItem');
	
	if (url){
		link.attr({href:url});
		link.addClass('clickable');
	}
	
	var label = $('<div>').addClass('label').html(label).appendTo(link);
	
	if (highlight)
		label.addClass('highlighted');
	
	$('<div>').addClass('details').html(details).appendTo(link);
	
	return createRow(link);
}

function createAction(type, value){
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
		value = value.replace(/\s+/g,' ');
		link = 'http://maps.google.fr/maps?q='+value;
	} else if (type == "website") {
		message = "Visiter son site";
		if (!value.match(/:\/\//))
			value = 'http://'+value;
		link = value;
	}
	
	return createItemRow(message, value, link);
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

function isFluent(languageLevel){
	return (languageLevel == 'Maternelle' || languageLevel == 'Courant' || languageLevel == 'Expert');
}

function isInterestReference(skill){
	return skill == 'Professionnel' || skill == 'Expert';
}

function sortInterests(interests){
	
	function sortLevel(interests){
		var levels = ['Passionné','Intéressé','Curieux'];
		levels.reverse();
		
		var source = interests.concat();
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
	
	var skills = ['Professionnel','Expert','Éclairé','Débutant'];
	skills.reverse();
	
	var source = interests.concat();
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
