
var currentDate = getCurrentDate();
var listingDate = new Date();

var blockIndex = new Object();
var eventCount = 0;

var stopLoading = false;
var eventLoading = false;

function setLoadStatus(data){
	$('#loadStatus').html(data);
}

function resetLoadStatus(){
	setLoadStatus('<a href="javascript:loadNextEvents()">Charger plus d\'évènements...</a>');
}

function isBottom(){
	// compares to docheight-1 because of android browser bug.
	return ($(window).height()+$(window).scrollTop()) >= ($(document).height()-1);
}

function checkScrollToBottom(){
	if (isBottom()) {
		loadNextEvents();
		return true;
	}
	
	return false;
}

function loadNextEvents(){
	if (!eventLoading) {
		if (!stopLoading) {
			eventLoading = true;
			setLoadStatus('Chargement en cours...');
			$.getJSON("services/listEvents.php", {month:listingDate.getMonth()+1, year:listingDate.getFullYear()}, handleNewEvents);
		}
	}
}

function handleNewEvents(data){
	eventLoading = false;
	
	if (isDefined(data.loggedIn) && !data.loggedIn) {
		window.location.href = "login.html";
		return;
	}
	
	if (data.errorMessage)
		setErrorMessage(data.errorMessage);
	else {
		if (data.events.length == 0) {
			// found empty month, stop loading
			stopLoading = true;
			setLoadStatus("Plus d'autres évènements ensuite.");
		} else {
			listingDate = addMonth(listingDate);
			
			setLoggedIn(data.username);
			
			for (var i=0; i<data.events.length; i++)
				if (!isBefore(data.events[i].date, currentDate))
					addEvent(data.events[i]);
			
			resetLoadStatus();
		}
		
		checkScrollToBottom();
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
