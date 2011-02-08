var queue = new Array();

var currentDate = getCurrentDate();
var listingDate = new Date();

var blockIndex = new Object();
var eventCount = 0;

var stopLoading = false;
var eventLoading = false;
var queueInProcess = false;

var init = true;

function setLoadStatus(data){
	$('#loadStatus').html(data);
}

function resetLoadStatus(){
	setLoadStatus('<a href="javascript:loadNextEvents()">Charger plus d\'évènements...</a>');
}

function isBottom(){
	// compares to docheight-1 because of dezsire Z browser bug.
	return ($(window).height()+$(window).scrollTop()) >= ($(document).height()-1);
}

function checkScrollToBottom(){
	if (isBottom()) {
		loadNextEvents();
		return true;
	}
	
	init = false;
	return false;
}

function loadNextEvents(){
	if (!eventLoading) {
		if (!stopLoading) {
			eventLoading = true;
			setLoadStatus('Chargement en cours...');
			$.getJSON("services/listEvents.php", {month:listingDate.getMonth()+1, year:listingDate.getFullYear()}, handleNewEvents);
		} else {
			processQueue(); // in case max loading happens because screen is too tall.
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
			init = false;
			
			setLoadStatus("Plus d'autres évènements ensuite.");
			processQueue();
		} else {
			listingDate = addMonth(listingDate);
			
			setLoggedIn(data.username);
			
			for (var i=0; i<data.events.length; i++)
				if (!isBefore(data.events[i].date, currentDate))
					addEvent(data.events[i]);
			
			resetLoadStatus();
		}
		
		// process queue only if not at bottom ie. not reloading events.
		if (!init || !checkScrollToBottom())
			processQueue();
	}
}

function addEvent(eventData){
	var dateBlock = blockIndex[eventData.date];
	var first = false;
	
	if (!dateBlock) {
		dateBlock = createDateBlock(eventData.date);
		first = true;		
	}
	
	var eventDiv = createListItem(eventData.title, '', null, 'event.html?eventId='+eventData.id);
	
	eventDiv.setFirst(first);
	eventDiv.setId('item-event-'+eventData.id);
	
	eventDiv.hide().appendTo(dateBlock).fadeIn(500);
	queue.push(eventData.id);
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

function processQueue(){
	if (queue.length == 0 || queueInProcess)
		return;
	
	queueInProcess = true;
	loadEventData(queue.shift(), processQueue);
}

function openEvent(eventId) {
	window.location.href = "event.html?eventId="+eventId;
}

function loadEventData(eventId, callback) {
	var itemDesc = $('#item-event-'+eventId+' '+'.listItemDetails');
	itemDesc.html('<img src="images/loading.gif">');
	
	$.getJSON("services/getEventData.php", {"eventId":eventId}, function(data){
		var details = "";
		
		if (data.errorMessage)
			details += "Erreur : "+data.errorMessage;
		else {
			details += data.participants.length+" / ";
			
			if (data.maxParticipants > 0)
				details += data.maxParticipants;
			else if (data.maxParticipants < 0)
				details += "illimité";
			else
				details += "inconnu (ERREUR)";
			
			details += " - ";
			details += data.author;
		}
		
		itemDesc.hide().html(details).fadeIn(200);

		if (data.isParticipating)
			$('#item-event-'+eventId+' '+'.listItemTitle').addClass("highlightedItem");
		
		queueInProcess = false;

		if (callback)
			callback();
	});
}
