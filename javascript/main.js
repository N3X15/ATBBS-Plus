function highlightReply(id)
{
	var divs = document.getElementsByTagName('div');

	for (var i = 0; i < divs.length; i++)
	{
		if (divs[i].className.indexOf('body') != -1)
			divs[i].className = divs[i].className.replace(/highlighted/, '');
	}

	if (id)
		document.getElementById('reply_box_' + id).className += ' highlighted';
}

function focusId(id)
{
	document.getElementById(id).focus();
	init();
}

function checkOrUncheckAllCheckboxes()
{
	tmp = document.fuck_off;

	for (i = 0; i < tmp.elements.length; i++)
	{
		if (tmp.elements[i].type == 'checkbox')
		{
			if (tmp.master_checkbox.checked == true)
				tmp.elements[i].checked = true;
			else
				tmp.elements[i].checked = false;
		}
	}
}

function submitDummyForm(theAction, theVariableName, theVariableValue, confirmMessage)
{
	if (confirmMessage === undefined)
		var tmp = confirm('Really?');
	else
		var tmp = confirm(confirmMessage);

	if (tmp)
	{
		var form = document.getElementById('dummy_form');
		form.action = theAction;
		form.some_var.name = theVariableName;
		form.some_var.value = theVariableValue;
		form.submit();
	}

	return false;
}

function updateCharactersRemaining(theInputOrTextarea, theElementToUpdate, maxCharacters)
{
	tmp = document.getElementById(theElementToUpdate);
	tmp.firstChild.data = maxCharacters - document.getElementById(theInputOrTextarea).value.length;
}

function printCharactersRemaining(idOfTrackerElement, numDefaultCharacters)
{
	document.write(' (<STRONG ID="' + idOfTrackerElement + '">' + numDefaultCharacters + '</STRONG> characters left)');
}

function removeSnapbackLink()
{
	var tmp = document.getElementById("snapback_link");

	if (tmp)
		tmp.parentNode.removeChild(tmp);
}

function createSnapbackLink(lastReplyId)
{
	removeSnapbackLink();

	var div = document.createElement('DIV');
	div.id = 'snapback_link';
	var a = document.createElement('A');
	a.href = '#reply_' + lastReplyId;
	a.onclick = function () { highlightReply(lastReplyId); removeSnapbackLink(); };
	a.className = 'help_cursor';
	a.title = 'Click me to snap back!';
	var strong = document.createElement('STRONG');
	strong.appendChild(document.createTextNode('↕'));
	a.appendChild(strong);
	div.appendChild(a);
	document.body.appendChild(div);
}

function init()
{
	if (document.getElementById(window.location.hash.substring(1)) && window.location.hash.indexOf('reply_') != -1)
		highlightReply(window.location.hash.substring(7));
	else if (window.location.hash.indexOf('new') != -1)
		highlightReply(document.getElementById('new_id').value);
}

window.onload = init;
