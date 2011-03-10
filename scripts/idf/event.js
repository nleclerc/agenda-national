function loadIdfEvent(hash){
	if (hash && hash.match(/^#[a-z]+:\d+$/i)) {
		var eventId = hash.replace(/^#[a-z]+:(\d+)$/i, '$1');
		var regionId = hash.replace(/^#([a-z]+):\d+$/i, '$1');
		
		callService("idf-getEventData", {eventId: eventId}, function(data, user){
			handleIdfEventData(data, user, regionId);
		});
	}
	else
		setErrorMessage('Evènement non spécifié.');
}

function handleIdfEventData(data, currentUser, regionId) {
	// decode event title because of potential html entities.
	var decodedTitlePotentialyDangerous = decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(data.title);
	
	document.title = decodedTitlePotentialyDangerous+' [Agenda Mensa]';
	
	eventDate = data.start_date;
	
	eventId = data.id;
	userId = currentUser.id;
	
	var eventTable = $('<table>').attr({id:'eventBody'});
	var headerCell = $('<th>').attr({colspan:2}).appendTo($('<tr>').appendTo(eventTable));
	$('<span>').attr({id:'eventTitle'}).html(data.title).appendTo(headerCell);
	insertBackButton(headerCell, './#'+regionId+':'+getMonthFromDate(eventDate));
	
	var authorLink = $('<a>').html(data.author.name);
	if (data.author.id != 0)
		authorLink.attr({href:'member.html#'+data.author.id});
	else if (data.author.email)
		authorLink.attr({href:'mailto:'+data.author.email+'?subject=[MENSA-AGENDA] '+decodedTitlePotentialyDangerous});
	
	$('<span>').addClass('subtitle').text(' par ').append(authorLink).appendTo(headerCell);
	
	eventTable.append('<tr><td class="header">Description</td><td class="header">Participants ( '+
			data.participants.length+' / '+
			formatMaxParticipants(data.max_participants)+' )</td></tr>');
	
	var bodyRow = $('<tr>').appendTo(eventTable);
	
	var description = $('<td>').append(
		$('<eventDetails>').append(
			$('<date>').text(formatLongDate(eventDate))
		).append(
			$('<region>').text('Région '+data.region_id)
		)
	).append($('<div>').attr({id:'eventDescription'}).html(formatDescription(data.description))).appendTo(bodyRow);
	
	var participantTable = $('<table>').attr({id:'participantTable'}).appendTo($('<td>').attr({id:'participantColumn'}).appendTo(bodyRow));
	
	for (var i=0; i<data.participants.length; i++) {
		var currentParticipant = data.participants[i];
		var row = $('<tr>').appendTo(participantTable);
		var name = $('<div>').addClass('participantName').html(currentParticipant.name);
		var details = $('<div>').addClass('participantDetails').text(currentParticipant.region_id+' #'+currentParticipant.id);
		
		if (currentParticipant.id == currentUser.id)
			name.addClass('highlighted');
		
		if (currentParticipant.email)
			details.append(' - '+currentParticipant.email);
		
		$('<a>').attr({href:'member.html#'+currentParticipant.id}).addClass('participantLink').append(name).append(details).appendTo($('<td>').appendTo(row));
	}
	
	var controlBar = $('<div id="controlBar">'+
			'<button id="subscribeButton" disabled="true" onclick="subscribeIdf()">S\'inscrire</button>'+
			'<button id="unsubscribeButton" disabled="true" onclick="unsubscribeIdf()">Se désinscrire</button>'+
			'</div>');
	
	if (data.author_id == currentUser.id)
		$('<button>').attr({id:'editButton'}).text('Editer').click(function(){
			jumpTo('idf-eventEdit.html#'+regionId+':'+data.id);
		}).appendTo(controlBar);
	
	description.append(controlBar);
	
	setMainContent(eventTable);
	
	if (data.is_participating) {
		$('#eventDetails').addClass('participatingEvent');
		enable($('#unsubscribeButton'));
	}
	else if (data.max_participants == 0 || data.max_participants > data.participants.length){
		enable($('#subscribeButton'));
	}
}

function subscribeIdf(){
	loadAndRefresh("services/idf-addParticipation", {eventId: eventId});
}

function unsubscribeIdf(){
	loadAndRefresh("services/idf-cancelParticipation", {eventId: eventId});
}
