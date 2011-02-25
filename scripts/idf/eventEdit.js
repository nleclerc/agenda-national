

function processEventEditHash(hash) {
	if (hash.match(/^#[a-z]{3}:\d{4}-\d{2}-\d{2}$/i)){
		var date = hash.substr(5);
		var regionId = hash.substr(1,3);
		setEditEventDate(date);
		setCancelLink('./#'+regionId+':'+getMonthFromDate(date)); // go to month page.
		enableSubmit();
	} else if (hash.match(/^#[a-z]{3}:\d+$/i)) {
		var regionId = hash.substr(1,3);
		setCancelLink('idf-event.html'+hash); // go to event page.
		callService("idf-getEventData", {eventId: hash.substr(5)}, function(data){
			handleEditEventData(data, regionId);
		});
	} else {
		setCancelLink('.'); // go to home.
		setErrorMessage("Date ou évènement non spécifiés.");
	}
}

function submitEventValues() {
	var data = {
		start_date: $('#dateInput').val(),	
		title: $('#titleInput').val(),
		description: $('#descriptionInput').val(),	
		max_participants: $('#maxParticipantsInput').val()
	};
	
	var currentId = $('#eventIdInput').val();
	
	if (currentId)
		data.id = currentId;
	
	callService('idf-setEventData', {eventData: JSON.stringify(data)}, function(){
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

function handleEditEventData(eventData, regionId) {
	setEditEventDate(eventData.start_date.replace(/^([\-0-9]*) .*$/, '$1'));
	$('#eventIdInput').val(eventData.id);
	$('#titleInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.title));
	$('#descriptionInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.description));
	$('#maxParticipantsInput').val(eventData.max_participants);
	
	enableDelete(eventData.id, './#'+regionId+':'+getMonthFromDate(eventData.start_date));
	enableSubmit();
}

function enableDelete(eventId, exitUrl){
	$('#deleteButton').unbind('click').click(function(){
		callService('idf-deleteEvent', {eventId: eventId}, function(){jumpTo(exitUrl);});
	}).show();
}

function setCancelLink(target){
	insertBackButton($('#backContainer'), target);
	$('#cancelLink').attr({href:target});
}