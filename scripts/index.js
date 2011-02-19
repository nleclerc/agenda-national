

var currentDate = getCurrentDate();
var listingDate = new Date();

function loadEvents(){
	$.getJSON("services/listEvents.php", {month:listingDate.getMonth()+1, year:listingDate.getFullYear()}, handleNewEvents);
}

function handleNewEvents(data){
	if (!data.loggedIn) {
		showLoginForm();
		return;
	}
	else
		setLoggedIn(data, true);
	
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else {
		for (var i=0; i<data.events.length; i++)
			if (!isBefore(data.events[i].date, currentDate))
				addEvent(data.events[i]);
	}
}

function addEvent(eventData){
	var dateBlock = blockIndex[eventData.date];
	var first = false;
	
	if (!dateBlock) {
		dateBlock = createDateBlock(eventData.date);
		first = true;		
	}
	
	var details = "";
	
	details += eventData.participantCount+" / ";
	
	if (eventData.maxParticipants > 0)
		details += eventData.maxParticipants;
	else
		details += "illimité";
	
	details += " - ";
	details += eventData.author;
	
	var eventDiv = createListItem(eventData.title, details, null, 'event.html?eventId='+eventData.id, false, eventData.isParticipating);
	
	eventDiv.setFirst(first);
	eventDiv.setId('item-event-'+eventData.id);
	
	eventDiv.hide().appendTo(dateBlock).fadeIn(500);
	eventCount++;
}

function createDateBlock(date){
	var events = $('#eventList');
	var dateBlock = $('<div id="block-'+date+'" class="list"></div>\n');
	blockIndex[date] = dateBlock;
	
	events.append('<div class="listDate">'+beautifyDate(date, currentDate)+'</div>\n');
	dateBlock.hide().appendTo(events).fadeIn(500);
	
	return dateBlock;
}

function openEvent(eventId) {
	window.location.href = "event.html?eventId="+eventId;
}
