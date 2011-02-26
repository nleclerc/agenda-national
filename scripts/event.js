var eventId = null;
var userId = null;

function loadEvent(hash){
	if (hash && hash.match(/^#[a-z]+:\d+$/i)) {
		var eventId = hash.replace(/^#[a-z]+:(\d+)$/i, '$1');
		var regionId = hash.replace(/^#([a-z]+):\d+$/i, '$1');
		
		callService("getEventData", {eventId: eventId}, function(data, user){
			handleEventData(data, user, regionId);
		});
	}
	else
		setErrorMessage('Evènement non spécifié.');
}

function handleEventData(data, currentUser, regionId) {
	// decode event title because of potential html entities.
	var decodedTitlePotentialyDangerous = decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(data.title);
	
	document.title = decodedTitlePotentialyDangerous+' [Agenda Mensa]';
	
	eventDate = data.start_date.replace(/^([^\s]+) .*$/, '$1');
	
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
			formatMaxParticipants(data.maxParticipants)+' )</td></tr>');
	
	var bodyRow = $('<tr>').appendTo(eventTable);
	
	var description = $('<td>').append(
		$('<eventDetails>').append(
			$('<date>').text(formatLongDate(eventDate)+' à '+getTime(data.start_date))
		).append(
			$('<region>').html('Région '+data.region_id)
		).append(
			$('<div>').attr({id:'locationLink'}).text('Lieu : ').append(
				$('<a>').attr({id:'locationLink', target:'_blank', href:getLocationLink(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(data.location))}).html(data.location)
			)
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
			'<button id="subscribeButton" disabled="true" onclick="subscribe()">S\'inscrire</button>'+
			'<button id="unsubscribeButton" disabled="true" onclick="unsubscribe()">Se désinscrire</button>'+
			'</div>');
	
	if (data.author_id == currentUser.id)
		$('<button>').attr({id:'editButton'}).text('Editer').click(function(){
			jumpTo('eventEdit.html#'+regionId+':'+data.id);
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

function formatDescription(source) {
	var result = source;
	
	result = result.replace(/&lt;\/?(b|string)(&gt;|>)/ig, '**'); // bold tags
	result = result.replace(/&lt;\/?(i|em)(&gt;|>)/ig, '*'); // italic tags
	
	// highlight some specific values.
	result = result.replace(/([^*]|^)(\d?\dh\d{0,2})([^*]|$)/ig, '$1**$2**$3'); // hours
	result = result.replace(/([^*]|^)(\d+[\.,]?\d*\s*(€|euros?))([^*]|$)/ig, '$1**$2**$4'); // price
	result = result.replace(/([^*]|^)(gratuite?(ment)?s?)([^*]|$)/ig, '$1**$2**$4'); // price
//	result = result.replace(/(ATTENTION)/g, '<span class="highlight">$1</span>');
//	result = result.replace(/(NOTE)/g, '<span class="highlight">$1</span>');
	
	// replace phone numbers with tel: links.
	result = result.replace(/([^\[]|^)((0\d)[.\- ]?(\d\d)[.\- ]?(\d\d)[.\- ]?(\d\d)[.\- ]?(\d\d))([^\]]|$)/gm, '$1[$2](tel:$3$4$5$6$7)$8');

	
	// replace url without protocol part.
	result = result.replace(/(\(\s*)(www.[^\s<"\)]+)(\s*\))/gim, '$1[$2]($2)$3'); // url between round brackets
	result = result.replace(/(^|[^>":\/\[])(www.[^\s<"]+)/gim, '$1[$2]($2)');
	
	// decode gt for blockquotes.
	result = result.replace(/&gt;/g, '>');
	
	var converter = new Showdown.converter();
	result = converter.makeHtml(result);
	
	return result;
}

function subscribe(){
	loadAndRefresh("services/addParticipation.php", {userId: userId, eventId: eventId});
}

function unsubscribe(){
	loadAndRefresh("services/cancelParticipation.php", {userId: userId, eventId: eventId});
}
