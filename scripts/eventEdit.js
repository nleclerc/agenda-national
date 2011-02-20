

function processEventEditHash(hash) {
	setCancelLink('.'); // go to home.
	
	if (hash.match(/^#\d{2}-\d{2}-\d{4}$/)){
		var date = hash.substr(1);
		setEditEventDate(date.replace(/-/g,'/'));
		var tokens = date.split('-');
		setCancelLink('./#'+tokens[2]+'-'+tokens[1]); // go to month page.
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
	enableSubmit();
}

function setCancelLink(target){
	$('#cancelLink').attr({href:target});
}