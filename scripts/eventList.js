

var currentDate = getCurrentDate();
var listingDate = new Date();

var monthLabels = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
var dayLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];

function loadEvents(hash){
	var listingDate = getCurrentReferenceDate(hash);
	
	var startDate = findStartDate(listingDate);
	var endDate = findEndDate(listingDate);

	getJSON("services/listEvents.php", {startDate:formatDate(startDate,'-'), endDate:formatDate(endDate,'-')}, function(data){
		if (location.hash != hash)
			location.hash = hash;
		
		buildEventTable(data, listingDate);
	});
}

function createMonthLink(year, month, label){
	var targetDate = new Date(year, month);
	return $('<button>').addClass('monthLink').text(label).click(function(){
		loadEvents('#'+targetDate.getFullYear()+'-'+getDoubleDigit(targetDate.getMonth()+1));
	});
}

function getCurrentReferenceDate(hash){
	var referenceDate = new Date();
	
	if (hash && hash.match(/^#\d{4}-\d{2}$/)) {
		var tokens = hash.substr(1).split('-');
		
		// radix is specified in parseint because of leading zeroes bug.
		// cf http://www.breakingpar.com/bkp/home.nsf/0/87256B280015193F87256C85006A6604
		referenceDate = new Date(tokens[0], parseInt(tokens[1],10)-1, 1);
	}
	
	return referenceDate;
}

function buildEventTable(data, referenceDate) {
	var events = data.events;
	
	var table = $('<table id="eventTable">');
	var globalHeader = $('<th colspan="7">').appendTo($('<tr>').appendTo(table));
	createMonthLink(referenceDate.getFullYear(), referenceDate.getMonth()-1, '<').appendTo(globalHeader);
	createMonthLink(referenceDate.getFullYear(), referenceDate.getMonth()+1, '>').appendTo(globalHeader);
	$('<span>').text(monthLabels[referenceDate.getMonth()]+' '+referenceDate.getFullYear()).appendTo(globalHeader);
	
	var dayHeaders = $('<tr>').appendTo(table);
	$(dayLabels).each(function(index, item){
		$('<td>').addClass('dayHeader').text(item).appendTo(dayHeaders);
	});
	
	var today = getToday();
	var startDate = findStartDate(referenceDate);
	var endDate = findEndDate(referenceDate);
	
	var currentRow = null;
	
	var cellIndex = new Object();
	
	for (var currentDate=startDate; currentDate<=endDate; currentDate=addDay(currentDate)) {
		if (isMonday(currentDate))
			currentRow = $('<tr>').appendTo(table);
		
		var currentCell = $('<td>').addClass('dayCell').appendTo(currentRow);
		$('<div>').addClass('dateLabel').appendTo(currentCell).append(
			$('<a>').attr({href:'eventEdit.html#'+formatDate(currentDate, '-'),title:'Ajouter un évènement'}).text(currentDate.getDate()).addClass('dateLabel')
		);
		
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
	
	setMainContent(table);
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
function findStartDate(referenceDate){
	var startDate = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), 1); // substract 1 to month because of 0 index.
	
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
function findEndDate(referenceDate){
	// not substracting 1 to month gets next month and day 0 gets last day of current month.
	var endDate = new Date(referenceDate.getFullYear(), referenceDate.getMonth()+1, 0);
	
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
