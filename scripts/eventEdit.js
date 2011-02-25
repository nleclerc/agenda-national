

function processEventEditHash(hash) {
	if (hash.match(/^#[a-z]{3}:\d{4}-\d{2}-\d{2}$/i)){
		var date = hash.substr(5);
		var regionId = hash.substr(1,3);
		setEditEventDate(date);
		setRegion(regionId);
		setCancelLink('./#'+regionId+':'+getMonthFromDate(date)); // go to month page.
		enableSubmit();
	} else if (hash.match(/^#[a-z]{3}:\d+$/i)) {
		var regionId = hash.substr(1,3);
		setCancelLink('event.html'+hash); // go to event page.
		callService("getEventData", {eventId: hash.substr(5)}, function(data){
			handleEditEventData(data, regionId);
		});
	} else {
		setCancelLink('.'); // go to home.
		setErrorMessage("Date ou évènement non spécifiés.");
	}
	
	setLocationPreviewLink();
}

function setRegion(selectedRegion){
	$('#regionSelector').val(selectedRegion);
	
	callService("listRegions", null, function(regions){
		var selector = $('#regionSelector').html('');
		
		for (var i=0; i<regions.length; i++) {
			var currentRegion = regions[i].id;
			var option = $('<option>').text(currentRegion).appendTo(selector);
			
			if (currentRegion == selectedRegion)
				option.attr({selected:'selected'});
		}
	});
}

function setLocationPreviewLink(){
	$('#locationInput').change($('#locationPreview').attr({href:getLocationLink($('#locationInput').val())}));
}

function submitEventValues() {
	var data = {
		start_date: $('#dateInput').val()+' '+$('#timeInput').val(),	
		title: $('#titleInput').val(),
		region_id: $('#regionSelector').val(),
		location: $('#locationInput').val(),	
		description: $('#descriptionInput').val(),	
		max_participants: $('#maxParticipantsInput').val()
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

function handleEditEventData(eventData, regionId) {
	setEditEventDate(eventData.start_date.replace(/^([\-0-9]*) .*$/, '$1'));
	setRegion(eventData.region_id);
	
	$('#eventIdInput').val(eventData.id);
	$('#timeInput').val(eventData.start_date.replace(/^.* (.*):\d{2}$/, '$1'));
	$('#locationInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.location));
	$('#titleInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.title));
	$('#descriptionInput').val(decodeHtmlEntitiesAndPotentialyInsertMaliciousCode(eventData.description));
	$('#maxParticipantsInput').val(eventData.max_participants);
	
	enableDelete(eventData.id, './#'+regionId+':'+getMonthFromDate(eventData.start_date));
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