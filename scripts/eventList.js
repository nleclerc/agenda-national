

var currentDate = getCurrentDate();
var listingDate = new Date();

var monthLabels = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
var dayLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];

function loadEvents(hash){
	var listingDate = getCurrentReferenceDate(hash);
	
	var region = getRegion(hash);
	
	parms = {
		startDate:formatDate(findStartDate(listingDate)),
		endDate:formatDate(findEndDate(listingDate))
	};
	
	if (region)
		parms.regionId = region;
	
	callService("listEvents", parms, function(data){
		if (location.hash != hash)
			location.hash = hash;
		
		buildEventTable(data, listingDate);
	});
}

function getRegion(hash) {
	if (hash && hash.match(/^#[a-z]{3}(:\d{4}-\d{2}$)?/i))
		return hash.replace(/^#([a-z]{3})(:\d{4}-\d{2}$)?/i, '$1');
	
	return null;
}

function createMonthLink(regionId, year, month, label){
	return $('<button>').addClass('headerButton').text(label).click(function(){
		loadEvents('#'+regionId+':'+year+'-'+getDoubleDigit(month+1));
	});
}

function getCurrentReferenceDate(hash){
	var referenceDate = new Date();
	
	if (hash && hash.match(/^#([a-z]{3}:)?\d{4}-\d{2}$/i)) {
		var tokens = hash.replace(/^#([a-z]{3}:)?(\d{4}-\d{2})$/i, '$2').split('-');
		
		// radix is specified in parseint because of leading zeroes bug.
		// cf http://www.breakingpar.com/bkp/home.nsf/0/87256B280015193F87256C85006A6604
		referenceDate = new Date(tokens[0], parseInt(tokens[1],10)-1, 1);
	}
	
	return referenceDate;
}

function buildEventTable(data, referenceDate) {
	var events = data.events;
	
	var title = monthLabels[referenceDate.getMonth()]+' '+referenceDate.getFullYear();
	document.title = title+' [Agenda Mensa]';
	
	var currentRegion = data.region_id;
	
	var table = $('<table id="eventTable">');
	var globalHeader = $('<th colspan="7">').appendTo($('<tr>').appendTo(table));
	createMonthLink(currentRegion, referenceDate.getFullYear(), referenceDate.getMonth()-1, '<').appendTo(globalHeader);
	createMonthLink(currentRegion, referenceDate.getFullYear(), referenceDate.getMonth()+1, '>').appendTo(globalHeader);
	
	globalHeader.append(
		$('<span>').text(title)
	).append(' ').append(
		$('<span>').addClass('subtitle').text(data.region_id)
	);
	
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
		
		var dayLabel = $('<a>').addClass('dateLabel').attr(
				{href:'eventEdit.html#'+currentRegion+':'+formatDate(currentDate, '-'),title:'Ajouter un évènement',day:currentDate.getDate()}
			).text(currentDate.getDate())
			.mouseover(function(event){$(event.target).prepend('Ajouter > ');})
			.mouseout(function(event){
				var target = $(event.target);
				target.text(target.attr('day'));
			})
			.appendTo(currentCell);
		
		if (currentDate.getMonth() == referenceDate.getMonth())
			currentCell.addClass('currentMonth');
		else
			currentCell.addClass('paddingMonth');
		
		if (currentDate.getTime() == today.getTime())
			currentCell.addClass('today');
		
		cellIndex[formatDate(currentDate)] = $('<ul>').appendTo(currentCell);
	}
	
	for (var i=0; i<events.length; i++) {
		if (events[i].is_idf_event)
			addIdfEvent(events[i], cellIndex, currentRegion);
		else
			addEvent(events[i], cellIndex, currentRegion);
	}
	
	setMainContent(table);
}

function addIdfEvent(eventData, cellIndex, regionId) {
	var link = $('<a>').attr({href:'idf-event.html#'+regionId+':'+eventData.id}).addClass('eventLink').html(
		' '+eventData.title
	);
	
	if (eventData.is_participating)
		link.addClass('highlighted');
	
	$('<li>').append(link).appendTo(cellIndex[eventData.start_date.replace(/ .+$/, '')]);
}

function addEvent(eventData, cellIndex, regionId) {
	var link = $('<a>').attr({href:'event.html#'+regionId+':'+eventData.id}).addClass('eventLink').html(
		' '+eventData.title
	).prepend(
		$('<time>').html(eventData.start_date.replace(/^.+ (.+):\d{2}$/, '$1'))
	);
	
	if (eventData.is_participating)
		link.addClass('highlighted');
	
	$('<li>').append(link).appendTo(cellIndex[eventData.start_date.replace(/ .+$/, '')]);
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

function createDateBlock(date){
	var events = $('#eventList');
	var dateBlock = $('<div id="block-'+date+'" class="list"></div>\n');
	blockIndex[date] = dateBlock;
	
	events.append('<div class="listDate">'+beautifyDate(date, currentDate)+'</div>\n');
	dateBlock.hide().appendTo(events).fadeIn(500);
	
	return dateBlock;
}
