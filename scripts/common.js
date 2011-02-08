
function setLoggedIn(username){
	$('#headerTitle').html(username);
	$('#quitButton').show();
}

function isDefined(varname){
	return typeof(varname) != "undefined";
}

function createListItem(title, details, icon, link, isSubseq, isHighlighted, listName, itemId){
	var item = $('<a class="listItem listItemForcedHeight"></a>');
	
	if (listName && itemId)
		item.attr('id', 'item-'+listName+'-'+itemId);
	
	if (isHighlighted)
		item.addClass('highlightedItem');
	
	if (isSubseq)
		item.addClass('subseqListItem');
	
	if (link) {
		item.attr('href', link);
		item.addClass('handPointer'); // fix for missing hand pointer in internet explorer.
		
		if (link.indexOf(':') < 0)
			item.click(function(){jumpTo(link);return false;});
	}
	
	if (icon)
		$('<img src="images/'+icon+'.png" class="listItemIcon">').appendTo(item);
	
	var titleDiv = $('<span class="listItemTitle lineBlock">'+title+'</span>').appendTo(item);
	var detailsDiv = $('<span class="listItemDetails lineBlock">'+details+'</span>').appendTo(item);
	
	item.titleNode = titleDiv;
	item.detailNode = detailsDiv;
	
	item.highlight = function(flag) {
		var doSet = true;
		
		if (typeof(flag) == 'boolean')
			doSet = flag;
		
		if (doSet)
			this.titleNode.addClass('highlightedItem');
		else
			this.titleNode.removeClass('highlightedItem');
		return this;
	};
	
	item.setFirst = function(flag) {
		var doSet = true;
		
		if (typeof(flag) == 'boolean')
			doSet = flag;
		
		if (doSet)
			this.removeClass('subseqListItem');
		else
			this.addClass('subseqListItem');
		return this;
	};
	
	item.setSubsequent = function() {this.setFirst(false);return this;};
	
	item.setSingleLine = function() {
		this.removeClass('listItemForcedHeight');
		this.titleNode.removeClass('lineBlock');
		this.detailNode.removeClass('lineBlock');
		this.detailNode.addClass('leftGap');
		return this;
	};
	
	item.setId = function(newId){this.attr('id', newId);return this;};
	
	return item;
}

function jumpTo(url){
	window.location.href=url;
}

function getCurrentDate(){
	return formatDate(new Date());
}

function getDoubleDigit(number){
	var result = ''+number;
	
	while (result.length < 2)
		result = '0'+result;
	
	return result;
}

function loadAndRefresh(url, args, callback){
	$.ajax({
		url: url,
		dataType: 'json',
		data: args,
		success: function(data, textStatus, xhr){
			if (callback)
				callback(data, textStatus, xhr);
			
			window.location.reload();
		}
	});
}

function logout(){
	loadAndRefresh("services/logout.php");
}

function formatMaxParticipants(count){
	if (count > 0)
		return ''+count;
	else if (count < 0)
		return "illimité";
	else
		return "inconnu (ERREUR)";
}

function formatDate(date) {
	return getDoubleDigit(date.getDate())+'/'+getDoubleDigit(date.getMonth()+1)+'/'+date.getFullYear();
}

var daysOfWeek = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

function getWeekDay(datestr){
	var date = new Date(datestr.match(/\d+$/), datestr.match(/\/\d+\//).toString().match(/\d+/)-1, datestr.match(/^\d+/));
	return daysOfWeek[date.getDay()];
}

function beautifyDate(date, referenceDate){
	var result = date+' : '+getWeekDay(date);
	
	if (referenceDate == date)
		result = "Aujourd'hui, "+result;;
	
	return result;
}

function setErrorMessage(message){
	var errorZone = $("#errorMessage");
	errorZone.addClass("errorMessage");
	errorZone.html(message);
}

function addMonth(date){
	return new Date(date.getFullYear(), date.getMonth()+1, 1);
}

function isBefore(testedDate, referenceDate){
	return parseDate(testedDate) < parseDate(referenceDate);
}

function parseDate(dateString){
	var dateParts = dateString.split("/");
	return new Date(dateParts[2], dateParts[1]-1, dateParts[0]);
}

function enable(element){
	element.removeAttr('disabled');
}

function disable(element){
	element.attr('disabled', 'true');
}