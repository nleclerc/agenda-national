

var currentDate = getCurrentDate();
var listingDate = new Date();

var monthLabels = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
var dayLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];

function loadEvents(){
	getJson("services/listEvents.php", {month:listingDate.getMonth()+1, year:listingDate.getFullYear()}, buildEventTable);
}

function buildEventTable(data) {
	var events = data.events;
	
	var table = $('<table id="eventTable">');
	$('<tr>').appendTo(table).append($('<th colspan="7">').text(monthLabels[data.month-1]+' '+data.year));
	
	var dayHeaders = $('<tr>').appendTo(table);
	$(dayLabels).each(function(index, item){
		$('<td>').addClass('dayHeader').text(item).appendTo(dayHeaders);
	});
	
	var today = getToday();
	var referenceDate = new Date(data.year, data.month-1, 1);
	var startDate = findStartDate(data.year, data.month);
	var endDate = findEndDate(data.year, data.month);
	
	var currentRow = null;
	
	var cellIndex = new Object();
	
	for (var currentDate=startDate; currentDate<=endDate; currentDate=addDay(currentDate)) {
		if (isMonday(currentDate))
			currentRow = $('<tr>').appendTo(table);
		
		var currentCell = $('<td>').addClass('dayCell').appendTo(currentRow);
		$('<div>').text(currentDate.getDate()).addClass('dateLabel').appendTo(currentCell);
		
		if (currentDate.getMonth() == referenceDate.getMonth())
			currentCell.addClass('currentMonth');
		else
			currentCell.addClass('paddingMonth');
		
		if (currentDate.getTime() == today.getTime())
			currentCell.addClass('today');
		
		cellIndex[formatDate(currentDate)] = $('<ul>').appendTo(currentCell);
	}
	
	for (var i=0; i<events.length; i++) {
		var currentEvent = events[i];
		var link = $('<a>').attr({href:'event.html#'+currentEvent.id}).addClass('eventLink').html(currentEvent.title);
		
		if (currentEvent.isParticipating)
			link.addClass('highlighted');
		
		$('<li>').append(link).appendTo(cellIndex[currentEvent.date]);
	}
	
	$('#mainContent').html('').append(table);
}

function isMonday(date){
	return date.getDay() == 1;
}

function isSunday(date){
	return date.getDay() == 0;
}

/**
 * Looks for monday from first day of the month and before.
 * 
 * @param year
 * @param month
 * @returns {Date}
 */
function findStartDate(year, month){
	var startDate = new Date(year, month-1, 1); // substract 1 to month because of 0 index.
	
	while (!isMonday(startDate))
		startDate = removeDay(startDate);
	
	return startDate;
}

/**
 * Looks for sunday from last day of the month and after.
 * 
 * @param year
 * @param month
 * @returns {Date}
 */
function findEndDate(year, month){
	// not substracting 1 to month gets next month and day 0 gets last day of current month.
	var endDate = new Date(year, month, 0);
	
	while (!isSunday(endDate))
		endDate = addDay(endDate);
	
	return endDate;
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
