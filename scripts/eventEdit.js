

function processEventEditHash(hash) {
	if (hash.match(/^#[a-z]{3}:?$/i)) {
		var regionId = hash.substr(1,3);
		setEditEventDate(formatDate(new Date()));
		setRegion(regionId);
		setCancelLink('./#'+regionId); // go to month page.
		enableSubmit();
	} else if (hash.match(/^#[a-z]{3}:\d{4}-\d{2}-\d{2}$/i)) {
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
		setEditEventDate(formatDate(new Date()));
		setRegion('FRA');
		setCancelLink('./#'+regionId); // go to month page.
		enableSubmit();
	}
	
	setLocationPreviewLink();
}

function reformatDateInput (event){
	var input = $(event.target);
	var value = input.val();
	
	if (value.match(/\d{1,2}\/\d{1,2}\/\d{3,4}/)) {
		var tokens = value.split('/');
		input.val(getDoubleDigit(tokens[0])+'/'+getDoubleDigit(tokens[1])+'/'+tokens[2]);
	}
}

function toDisplayDate(datestr){
	return datestr.replace(/^(\d{4})-(\d{2})-(\d{2})$/,'$3/$2/$1');
}

function fromDisplayDate(datestr){
	return datestr.replace(/^(\d{2})\/(\d{2})\/(\d{4})$/,'$3-$2-$1');
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
	$('#locationInput').change(function(){
		$('#locationPreview').attr('href', getLocationLink($('#locationInput').val()));
	}).change();
}

function submitEventValues() {
	var data = {
		start_date: fromDisplayDate($('#dateInput').val())+' '+$('#timeInput').val(),	
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
		if (currentId)
			// if editing existing event, go back to event page.
			jumpTo('event.html#'+data.region_id+':'+currentId);
		else
			// else go to month and region of saved event.
			jumpTo('./#'+data.region_id+':'+getMonthFromDate(data.start_date));
	});
}

function enableSubmit() {
	enable($('#submitButton').unbind('click').click(submitEventValues));
}

function setEditEventDate(dateStr) {
	$('#dateInput').val(toDisplayDate(dateStr));
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
	
	setLocationPreviewLink(); // link is not updated on programmatic change.
	
	enableDelete(eventData.id, './#'+regionId+':'+getMonthFromDate(eventData.start_date));
	enableSubmit();
}

function enableDelete(eventId, exitUrl){
	$('#deleteButton').unbind('click').click(function(){
		callService('deleteEvent', {eventId: eventId}, function(){jumpTo(exitUrl);});
	}).show();
}

function setCancelLink(target){
	insertBackButton($('#backContainer').html(''), target);
	$('#cancelLink').attr({href:target});
}