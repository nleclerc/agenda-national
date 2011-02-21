
var monthLabels = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
var dayLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];


function initialize(){
	$.ajaxSetup({
		error:function(x){
			setErrorMessage('Erreur serveur: '+x.status);
		}
	});
	
	$('#title').append('<a href="http://fr.wikipedia.org/wiki/Version_d%27un_logiciel#Version_b.C3.AAta" id="betaLink">Beta</a>');
	
	$(document.body).append(
		'<div id="footer">'+
		'<a href="http://mensa.fr/">Mensa France</a>'+
		'<a href="mailto:nl@spirotron.fr?subject=[MENSA-AGENDA] Remarque">Contact</a>'+
		'<a href="https://github.com/mensa-france/agenda-national">Code Source</a>'+
		'</div>'
	);
}

function setMainContent(node){
	$('#mainContent').html('').append(node);
}

function getJSON(url, parms, callback) {
	setErrorMessage();
	$.getJSON(url, parms, function(data){
		if (processLogin(data))
			callback(data);
	});
}

function processLogin(data) {
	if (!data.loggedIn) {
		jumpTo('login.html');
		return false;
	}
	else
		setLoggedIn(data);
	
	if (data.errorMessage) {
		setErrorMessage(data.errorMessage);
		return false;
	}
	
	return true;
}

function setLoggedIn(data) {
	var authZone = $('#authenticationZone');
	authZone.html('');
	
	authZone.append($('<div id="memberName">').html(data.username));
	authZone.append($('<input>').attr({type:'submit',id:'logoutButton'}).val('Déconnection').click(logout));
}

function getMonthFromDate(date, separator){
	if(!separator)
		separator = '/';
	
	var tokens = date.split(separator);
	return tokens[2]+'-'+tokens[1];
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

function getToday(){
	var today = new Date();
	return new Date(today.getFullYear(), today.getMonth(), today.getDate());
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
	
	return "illimité";
}

function formatDate(date, separator) {
	if (!separator)
		separator = '/';
	return getDoubleDigit(date.getDate())+separator+getDoubleDigit(date.getMonth()+1)+separator+date.getFullYear();
}

var daysOfWeek = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

function getWeekDay(datestr){
	var date = new Date(datestr.match(/\d+$/), datestr.match(/\/\d+\//).toString().match(/\d+/)-1, datestr.match(/^\d+/));
	return daysOfWeek[date.getDay()];
}

function setErrorMessage(message){
	var errorZone = $("#errorMessage");
	if(message){
		errorZone.addClass("errorMessage");
		errorZone.html(message);
		errorZone.show();
	}
	else
		errorZone.hide();
}

function removeDay(ref){
	return modDate(ref, -1);
}

function addDay(ref){
	return modDate(ref, 1);
}

function modDate(ref, dayDelta, monthDelta, yearDelta){
	if (!yearDelta)
		yearDelta = 0;
	if (!monthDelta)
		monthDelta = 0;
	if (!dayDelta)
		dayDelta = 0;
	
	return new Date(ref.getFullYear()+yearDelta, ref.getMonth()+monthDelta, ref.getDate()+dayDelta);
}

function decodeHtmlEntities(str){
	return $('<div>').html(str).text();
}

function parseDate(dateString, separator){
	if (!separator)
		separator = '/';
	
	var dateParts = dateString.split(separator);
	return new Date(dateParts[2], dateParts[1]-1, dateParts[0]);
}

function formatLongDate(datestr, separator){
	var date = parseDate(datestr, separator);
	var result = '';
	result += dayLabels[date.getDay()];
	result += ' ';
	result += date.getDate();
	result += ' ';
	result += monthLabels[date.getMonth()];
	result += ' ';
	result += date.getFullYear();
	
	return result;
}

function insertBackButton(container, target){
	$('<button>').text('< Retour').click(function(){
		if (target)
			jumpTo(target);
		else
			history.back();
	}).prependTo(container);
}

function enable(element){
	element.removeAttr('disabled');
}

function disable(element){
	element.attr('disabled', 'true');
}

function hasPlaceholderSupport() {
	var i = document.createElement('input');
	return 'placeholder' in i;
}