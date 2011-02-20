var eventId = null;
var userId = null;

function loadEvent(hash){
	if (hash && hash.match(/#\d+/))
		getJSON("services/getEventData.php", {eventId: hash.substr(1)}, handleEventData);
	else
		setErrorMessage('Evènement non spécifié.');
}

function handleEventData(data) {
//	document.title = data.title+' ['+document.title+']';
	document.title = $('<div>').html(data.title).text()+' [Agenda Mensa]'; // decode event title because of current db format.
	
	eventId = data.id;
	userId = data.userid;
	
	var eventTable = $('<table>').attr({id:'eventBody'});
	var headerCell = $('<th>').attr({colspan:2}).appendTo($('<tr>').appendTo(eventTable));
	
	$('<span>').attr({id:'eventTitle'}).html(data.title).appendTo(headerCell);
	
	var authorLink = $('<a>').attr({id:'authorLink'}).html(data.author);
	if (data.authorEmail)
		authorLink.attr({href:'member.html#'+data.authorId});
	
	$('<span>').attr({id:'eventAuthor'}).text(' par ').append(authorLink).appendTo(headerCell);
	
	eventTable.append('<tr><td class="header">Description</td><td class="header">Participants ( '+
			data.participants.length+' / '+
			formatMaxParticipants(data.maxParticipants)+' )</td></tr>');
	
	var bodyRow = $('<tr>').appendTo(eventTable);
	
	var description = $('<td>').attr({id:'eventDescription'}).append(
		$('<div>').attr({id:'eventDate'}).text(formatLongDate(data.date))
	).append(formatDescription(data.description)).appendTo(bodyRow);
	
	var participantTable = $('<table>').attr({id:'participantTable'}).appendTo($('<tr>').attr({id:'participantColumn'}).appendTo(bodyRow));
	
	for (var i=0; i<data.participants.length; i++) {
		var currentParticipant = data.participants[i];
		var row = $('<tr>').appendTo(participantTable);
		var name = $('<div>').addClass('participantName').html(currentParticipant.name);
		var details = $('<div>').addClass('participantDetails').html(currentParticipant.id);
		
		if (currentParticipant.id == data.userid)
			name.addClass('highlighted');
		
		if (currentParticipant.email)
			details.append(' - '+currentParticipant.email);
		
		$('<a>').attr({href:'member.html#'+currentParticipant.id}).addClass('participantLink').append(name).append(details).appendTo($('<td>').appendTo(row));
	}
	
	description.append('<div id="controlBar">'+
			'<button type="button" id="subscribeButton" disabled="true" onclick="subscribe()">S\'inscrire</button>'+
			'<button type="button" id="unsubscribeButton" disabled="true" onclick="unsubscribe()">Se désinscrire</button>'+
			'</div>');
	
	setMainContent(eventTable);
	
	if (data.isParticipating) {
		$('#eventDetails').addClass('participatingEvent');
		enable($('#unsubscribeButton'));
	}
	else if (data.maxParticipants == 0 || data.maxParticipants > data.participants.length){
		enable($('#subscribeButton'));
	}
}

function formatLongDate(datestr){
	var date = parseDate(datestr);
	var result = '';
	result += dayLabels[date.getDay()];
	result += ' ';
	result += date.getDate();
	result += ' ';
	result += monthLabels[date.getMonth()];
	result += ' ';
	result += date.getFullYear();
	
	return result;
}

function formatDescription(source) {
	var result = source;
	
	// highlight some specific values.
	result = result.replace(/(\d?\dh\d{0,2})/ig, '<span class="highlight">$1</span>'); // hours
	result = result.replace(/(\d+[\.,]?\d*\s*(€|euros?))/ig, '<span class="highlight">$1</span>'); // price
	result = result.replace(/(gratuite?(ment)?s?)/ig, '<span class="highlight">$1</span>'); // price
//	result = result.replace(/(ATTENTION)/g, '<span class="highlight">$1</span>');
//	result = result.replace(/(NOTE)/g, '<span class="highlight">$1</span>');
	
	// replace phone numbers with tel: links.
	result = result.replace(/((0\d)[.\- ]?(\d\d)[.\- ]?(\d\d)[.\- ]?(\d\d)[.\- ]?(\d\d))/g, '<a href="tel:$2$3$4$5$6">$1</a>');
	
	// replace full url (including protocol part) 
	result = result.replace(/(\(\s*)((https?|ftp):\/\/[^\s<"\)]+)(\s*\))/gim, '$1<a href="$2">$2</a>$4'); // url between round brackets
	result = result.replace(/(^|[^>"])((https?|ftp):\/\/[^\s<"]+)/gim, '$1<a href="$2">$2</a>');
	
	// replace url without protocol part.
	result = result.replace(/(\(\s*)(www.[^\s<"\)]+)(\s*\))/gim, '$1<a href="http://$2">$2</a>$3'); // url between round brackets
	result = result.replace(/(^|[^>":\/])(www.[^\s<"]+)/gim, '$1<a href="http://$2">$2</a>');
	
	// replace email address with mailto link.
	result = result.replace(/([a-z0-9.\+\-]+@[a-z0-9.\-]+\.[a-z]+)/gim, '<a href="mailto:$1">$1</a>');
	
	// location hack using custom tag in html.
	result = result.replace(/<lieu>(.+?)<\/lieu>/gim, '<a href="http://maps.google.fr/maps?q=$1">$1</a>');

	// replace soft text linebreaks with br tags.
	result = result.replace(/\r?\n\r?/gim, '<br />\n');
	
	return result;
}

function getParticipantHtml(data, highlight, subseq){
	var details = ''+data.id;
	
	if (data.email)
		details += ' - '+data.email;
	
	return createListItem(data.name, details, 'person', 'member.html?memberId='+data.id, subseq, highlight);
}

function subscribe(){
	loadAndRefresh("services/subscribe.php", {userId: userId, eventId: eventId});
}

function unsubscribe(){
	loadAndRefresh("services/unsubscribe.php", {userId: userId, eventId: eventId});
}
