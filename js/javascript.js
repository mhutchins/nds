function mjh123(msg)
{
	alert(msg);
}

function ShowHide(layer_ref)
{ 
	//alert(state);

	var state = document.getElementById('fstate').value; 

	if (state == 'block')
		state = 'none'; 
	else
		state = 'block'; 


	document.getElementById('fstate').value=state; 

/*
	if (document.all) { //IS IE 4 or 5 (or 6 beta) 
		eval( "document.all." + layer_ref + ".style.display = state"); 
	} 
	if (document.layers) { //IS NETSCAPE 4 or below 
		document.layers[layer_ref].display = state; 
	} 
	if (document.getElementById &&!document.all) { 
*/

		hza = document.getElementById(layer_ref); 
		hza.style.display = state; 
	//} 
} 

function checkset(formId, cName) {
    for (i=0,n=formId.elements.length;i<n;i++)
        if (formId.elements[i].className.indexOf(cName) !=-1)
		formId.elements[i].checked = true;
}

function checkclr(formId, cName) {
    for (i=0,n=formId.elements.length;i<n;i++)
        if (formId.elements[i].className.indexOf(cName) !=-1)
		formId.elements[i].checked = false;
}

function checkinv(formId, cName) {
    for (i=0,n=formId.elements.length;i<n;i++)
        if (formId.elements[i].className.indexOf(cName) !=-1)
            if (formId.elements[i].checked == true)
				formId.elements[i].checked = false;
			else
				formId.elements[i].checked = true;

}


function bigcheck(id)
{
	if (document.getElementById(id).checked ==true)
		document.getElementById(id).checked=false;
	else
		document.getElementById(id).checked=true;
	
	event.returnValue=false;
	return false;
}

function submitRating(evt)
{
	var tmp = evt.target.getAttribute('id').substr(5);
	var widgetId = tmp.substr(0, tmp.indexOf('_'));
	var starNbr = tmp.substr(tmp.indexOf('_')+1);
	$.get("?action=vote",
	   { romid: widgetId, value: starNbr},
	   function(data){
		 //alert(data);
		 data++;
		 for(sn = 0 ; sn < NUMBER_OF_STARS ; sn++)
		 {
		    if (data >= 1)
			{
				document.getElementById('star_'+widgetId + '_' + sn).className = 'on';
				data --;
				continue;
			}
			if (data >= 0.5)
			{
				document.getElementById('star_'+widgetId + '_' + sn).className = 'half';
				data = 0;
				continue;
			}
			document.getElementById('star_'+widgetId + '_' + sn).className = 'off';
			
		 }
		 displayNormal(widgetId, NUMBER_OF_STARS);
	   }
	 );
}

function jqueryInit()
{
	init_rating();
	$('div.rating/img').bind('click', submitRating);
}


function remove(id)
{
	var d = document.getElementById('roms');
	var olddiv = document.getElementById(id);

//	var myElement = document.createElement("<div id=div_580 class='img'><a href='?action=details&userid=1&romid=580' ><img src='?action=getimg&blobid=4808' width=137 height=200 alt='Click for details'\></a><div class='desc'>42 All-Time Classics</div><div class='rom'>EUR </div><div class='rom' onclick='quickadd(580);return false;' ><a href='void(0)'>Add</a></div><div class='rating' id='rating_580'>3.5</div></div>");
	var newd = document.getElementById("mytarget");
	d.removeChild(olddiv);
//	d.appendChild("hr");
//	var newdiv = document.createElement('div');
//	newdiv.setAttribute('id', "martin");
//	newdiv.innterHTML = "Hello!";
//	newd.appendChild(newdiv);

}
function add()
{
	var d = document.getElementById('roms');
	var myElement = document.createElement("<div id=div_580 class='img'><a href='?action=details&userid=1&romid=580' ><img src='?action=getimg&blobid=4808' width=137 height=200 alt='Click for details'\></a><div class='desc'>42 All-Time Classics</div><div class='rom'>EUR </div><div class='rom' onclick='quickadd(580);return false;' ><a href='void(0)'>Add</a></div><div class='rating' id='rating_580'>3.5</div></div>");
	d.appendChild(myElement);
	document.getElementById("roms").appendChild(d);
}

var oldcol;

function resetcolor()
{
	document.getElementById('free').style.fontWeight="normal";
}

function quickadd(romid)
{
  var cardid = $('[@name="cardid"]').val();
  var userid = $('[@name="userid"]').val();
	$.get("nds.php?select[]="+romid,
	   { action: "quickadd", userid: userid, cardid: cardid },
	   function(data){
			document.getElementById('free').innerHTML=data;
//			oldcol = document.getElementById('free').style.backgroundColor;
			document.getElementById('free').style.fontWeight = "bold";
			setTimeout("resetcolor()",300);
			
			
			remove("div_" + romid);
//			add();
			
	   }
	 );
}


function submitform()
{
  document.myform.submit();
}

$(document).ready(jqueryInit);

