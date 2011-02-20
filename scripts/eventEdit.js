

function processEventEditHash(hash) {
	setCancelLink('.'); // go to home.
	
	if (hash.match(/^#\d{2}-\d{2}-\d{4}$/)){
		var date = hash.substr(1);
		setEditEventDate(date.replace(/-/g,'/'));
		setCancelLink('./#'+getMonthFromDate(date, '-')); // go to month page.
		enableSubmit();
	} else if (hash.match(/^#\d+$/)) {
		setCancelLink('event.html'+hash); // go to event page.
		getJSON("services/getEventData.php", {eventId: hash.substr(1)}, handleEditEventData);
	} else
		setErrorMessage("Date ou évènement non spécifiés.");
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
	
	getJSON('services/setEventData.php', {eventData: JSON.stringify(data)}, function(){
		// on save, activate cancel link.
		jumpTo($('#cancelLink').attr('href'));
	});
}

function enableSubmit() {
	$('#submitButton').unbind('click').click(submitEventValues).removeAttr('disabled');
}

function setEditEventDate(dateStr) {
	$('#eventDateLabel').text(formatLongDate(dateStr));
	$('#dateInput').val(dateStr);
}

function handleEditEventData(eventData) {
	setEditEventDate(eventData.date);
	$('#eventIdInput').val(eventData.id);
	$('#titleInput').val(decodeHtmlEntities(eventData.title));
	$('#descriptionInput').val(decodeHtmlEntities(eventData.description));
	$('#maxParticipantsInput').val(eventData.maxParticipants);
	
	enableDelete(eventData.id, './#'+getMonthFromDate(eventData.date,'/'));
	enableSubmit();
}

function enableDelete(eventId, exitUrl){
	$('#deleteButton').unbind('click').click(function(){
		getJSON('services/deleteEvent.php', {eventId: eventId}, function(){jumpTo(exitUrl);});
	}).show();
}

function getMonthFromDate(date, separator){
	var tokens = date.split(separator);
	return tokens[2]+'-'+tokens[1];
}

function setCancelLink(target){
	$('#cancelLink').attr({href:target});
}