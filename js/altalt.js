
/***************************************/
// vars for configuration

var background_color = "#FFFFAF";
var color = "#000000";
var border = " solid black 1px";
var fontfamily = "arial";
var fontsize = "12";
var hidetime = 2000; // time in millis before info fades
var padding = "5px 5px 5px 5px";

/***************************************/

var live = new Object();
var nofade = new Object();
var fastfade = false;
var moz_opacity = false;
var ie_opacity = false;
var nodes = new Object();

var tempX = 0;
var tempY = 0;

var ison = false;

var IE = document.all?true:false
// If NS -- that is, !IE -- then set up for mouse capture
if (!IE) document.captureEvents(Event.MOUSEMOVE)

// Set-up to use getMouseXY function onMouseMove
document.onmousemove = getMouseXY;

function setOff(altid) {
	//alert(altid);
	var nodeInst = nodes[altid];
	nodeInst['on'] = false;
	ison = false;
}

function showAltAlt(node, altid, width, closebox) {
	var status = true;
	var nodeInst = nodes[altid];
	if(nodeInst && nodeInst['on'] == true) {
		status = false;
	}

	if(browserDetect() && status) {
		var nodeInst = new Object();
		nodeInst['altid'] = altid;
		nodeInst['width'] = width;
		nodeInst['closebox'] = closebox;
		nodeInst['on'] = true;
		nodes[altid] = nodeInst;		
		node.onmouseout = function offit() { setOff(altid); };
		setTimeout("run('" + altid + "')", 500);	
	}
}

function run(altid) {
	var nodeInst = nodes[altid];
	if(nodeInst['on'] == true) {
		altalt(nodeInst['altid'], nodeInst['width'], nodeInst['closebox']);
	}
}

function hideAltAlt(altid) {
	fadeit(altid, 10);
}

function altalt(altid, width, closebox) {
	
	nid = altid + '-altalt';	
	try {				
		if(live[nid]) {
			return;
		} else {
			live[nid] = true;
		}
		
		leftx = tempX;
		bottomy = tempY;	
		
		var span = document.getElementById(altid).getElementsByTagName("span")[0];
		var newDiv = document.createElement("DIV");
			
		newDiv.setAttribute("id", nid);
		newDiv.style.position = "absolute";
		newDiv.style.visibility = "hidden"
		newDiv.style.overflow = "hidden";
		newDiv.style.top =  bottomy + "px";
		newDiv.style.left = leftx + "px";
		newDiv.style.border = border;
		newDiv.style.width = width + "px";
		newDiv.style.padding = padding;
		newDiv.style.fontFamily = fontfamily;
		newDiv.style.fontSize = fontsize + "px";
		newDiv.style.paddingRight = "3px";
		newDiv.style.backgroundColor = background_color;
				
		var newSpan = document.createElement("SPAN");
		newSpan = span.cloneNode(true);
		newDiv.appendChild(newSpan);		
		
		var newP = buildInfoP();
		var newA = buildInfoAhref();
					
		if(closebox) {
			closeBox(newA, altid);
		} else {
			newA.href="javascript:dontFade('" + altid + "');";			
			//var xlink = document.createTextNode("don't hide info box");
			//newA.appendChild(xlink);		
			newA.style.border = "0px";
			newA.style.backgroundColor = background_color;
			newA.style.textDecoration = "underline";
		}
		newP.appendChild(newA);
		newSpan.appendChild(newP);
		
		var body = document.getElementsByTagName("body")[0];	
		body.appendChild(newDiv);					
		newDiv.style.visibility = 'visible';

		if(! closebox) {
			setTimeout('fadeit("' + altid + '", 10)', hidetime);
		}
		
	} catch(err) {	
		alert(err);
	}
}

function fadeit(altid, op) {
	
	nid = altid + '-altalt';
	var node = document.getElementById(nid);		
	if(nofade[nid] == true) {
		node.style.opacity = "1";
		nofade[nid] = false;
		return;
	}
	
	if(op > 0 && fastfade == false) {
		op = op - 1;
		if(ie_opacity) {
			var ieop = op * 10;		
			node.style.filter = "alpha(opacity="+ ieop + ")";
		} else if (moz_opacity) {
			//alert('mozo');
			var mozop = op * 10;
			node.style.MozOpacity= mozop + "/100";
		} else {
			node.style.opacity = "." + op;
		}
		fadefunct = 'fadeit("' + altid + '", ' + op + ')';
		setTimeout(fadefunct, 30);

	} else {
		var node = document.getElementById(nid);
		node.style.visibility = 'hidden';			
		var body = document.getElementsByTagName("body")[0];
		body.removeChild( node );	
		live[nid] = false;
	}	
}

function dontFade(altid) {
	nid = altid + '-altalt';
	nofade[nid] = true;
	
	var span = document.getElementById(altid).getElementsByTagName("span")[0];
	infoDiv = document.getElementById(nid);
	var newSpan = document.createElement("SPAN");
	newSpan = span.cloneNode(true);
	var oldspan = infoDiv.getElementsByTagName("span")[0];
	var pnid = nid + 'pid';
	infoDiv.removeChild(oldspan);
	
	var ahref = buildInfoAhref(); 
	closeBox(ahref, altid);
	var newP = buildInfoP();
	newP.appendChild(ahref);
	newSpan.appendChild(newP);
		
	infoDiv.appendChild(newSpan);
}

function buildInfoAhref() {
	var newA = document.createElement("A");
	newA.style.color = "#000000";
	newA.style.textDecoration = "none";
	newA.style.fontSize = "10px";
	newA.style.border = "solid black 1px";
	newA.style.padding = "0px 2px 0px 2px";
	newA.style.backgroundColor = "#FFFFFF";
	return newA;								
}

function closeBox(ahref, altid) {
	ahref.href="javascript:hideAltAlt('" + altid + "');";			
	var xlink = document.createTextNode("X");
	ahref.appendChild(xlink);
}

function buildInfoP() {
		var newP = document.createElement("P");
		newP.style.textAlign = "right";
		newP.style.margin = "5px 5px 0px 0px";
		return newP;
}

function browserDetect() {
	var isgood = false;
	var agt=navigator.userAgent.toLowerCase();
	var is_major = parseInt(navigator.appVersion);	
	if(agt.indexOf('safari')!=-1) {
		isgood = true;
	} else if((agt.indexOf('mozilla')!=-1) && (agt.indexOf('gecko')!=-1)) {
		
		var rvindex = agt.indexOf("rv:");
		var rv2end = agt.substring(rvindex + 3, agt.length);
		var parenIndex = rv2end.indexOf(")");
		var rev = rv2end.substring(0, parenIndex); 
		if(rev < 1.7) {
			fastfade = true;
			//alert(	moz_opacity );
		}
		isgood = true;
	} else if(agt.indexOf('opera')!=-1) {
		fastfade = true;
		isgood = true;
		//moz_opacity = true;
	} else if(agt.indexOf('msie')!=-1) {
		if(agt.indexOf('mac')!=-1) {	
			ie_opacity = false;
			fastfade = true;
		} else {
			ie_opacity = true;
		}
		isgood = true;
	}
	return isgood;
}


function getMouseXY(e) {
	if (IE) { // grab the x-y pos.s if browser is IE
    		tempX = event.clientX + document.body.scrollLeft
    		tempY = event.clientY + document.body.scrollTop
  	} else {  // grab the x-y pos.s if browser is NS
    		tempX = e.pageX
    		tempY = e.pageY
  	}
 }  
