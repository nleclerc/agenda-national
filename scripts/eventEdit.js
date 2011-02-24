

function processEventEditHash(hash) {
	
	if (hash.match(/^#\d{2}-\d{2}-\d{4}$/)){
		var date = hash.substr(1);
		setEditEventDate(date.replace(/-/g,'/'));
		setCancelLink('./#'+getMonthFromDate(date, '-')); // go to month page.
		enableSubmit();
	} else if (hash.match(/^#\d+$/)) {
		setCancelLink('event.html'+hash); // go to event page.
		callService("getEventData", {eventId: hash.substr(1)}, handleEditEventData);
	} else {
		setCancelLink('.'); // go to home.
		setErrorMessage("Date ou évènement non spécifiés.");
	}
}

function submitEventValues() {
	var data = {
		date: $('#dateInput').val(),	
		title: $('#titleInput').val(),	
		description: $('#descriptionInput').val(),	
		maxParticipants: $('#maxParticipantsInput').val()
	};
	
	var currentId = $('#eventIdInput').val();
	
	if (currentId)
		data.id = currentId;
	
	callService('setEventData', {eventData: JSON.stringify(data)}, function(){
		// on save, activate cancel link.
		jumpTo($('#cancelLink').attr('href'));
	});
}

function enableSubmit() {
	enable($('#submitButton').unbind('click').click(submitEventValues));
}

function setEditEventDate(dateStr) {
	$('#eventDateLabel').text(formatLongDate(dateStr));
	$('#dateInput').val(dateStr);
}

function handleEditEventData(eventData) {
	setEditEventDate(eventData.date);
	$('#eventIdInput').val(eventData.id);
	$('#titleInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.title));
	$('#descriptionInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.description));
	$('#maxParticipantsInput').val(eventData.maxParticipants);
	
	enableDelete(eventData.id, './#'+getMonthFromDate(eventData.date,'/'));
	enableSubmit();
}

function enableDelete(eventId, exitUrl){
	$('#deleteButton').unbind('click').click(function(){
		callService('deleteEvent', {eventId: eventId}, function(){jumpTo(exitUrl);});
	}).show();
}

function setCancelLink(target){
	insertBackButton($('#backContainer'), target);
	$('#cancelLink').attr({href:target});
}