function getHTTPObject()
{
   if (window.ActiveXObject)
        return new ActiveXObject("Microsoft.XMLHTTP");
   else if (window.XMLHttpRequest)
        return new XMLHttpRequest();
   else {
      alert("Your browser does not support AJAX.");
      return null;
   }
}

function doWork()
{
        httpObject = getHTTPObject();
    if (httpObject != null)
 {        httpObject.open("GET", "upperCase.php?inputText=" +document.getElementById('inputText').value, true);
        httpObject.send(null);
         httpObject.onreadystatechange = setOutput;
    }
}

function setOutput()
{
    if(httpObject.readyState == 4)
	{
        document.getElementById('outputText').value = httpObject.responseText;
    }
}

var httpObject = null;

function checkAll(formId, cName, check ) {
    for (i=0,n=formId.elements.length;i<n;i++)
        if (formId.elements[i].className.indexOf(cName) !=-1)
            formId.elements[i].checked = check;
}

function SaveScrollXY() {

    document.myform.ScrollX.value = document.body.scrollLeft;
    document.myform.ScrollY.value = document.body.scrollTop;
  }
  function ResetScrollPosition() {
    var hidx, hidy;
    hidx = document.myform.ScrollX;
    hidy = document.myform.ScrollY;
    if (typeof hidx != 'undefined' && typeof hidy != 'undefined') {
      self.scrollTo(hidx.value, hidy.value);

    }
  }