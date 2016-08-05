/* -----------------------------------------------
   Floating layer - v.1
   (c) 2006 www.haan.net
   contact: jeroen@haan.net
   You may use this script but please leave the credits on top intact.
   Please inform us of any improvements made.
   When usefull we will add your credits.
  ------------------------------------------------ */

var popupDivx = 0;
var popupDivy = 0;
function setVisible(obj, pos_x, pos_y)
{
	obj = document.getElementById(obj);
	popupDivx=pos_x;
	popupDivy=pos_y;
	obj.style.left=pos_x;
	obj.style.top=pos_y;
	obj.style.visibility = (obj.style.visibility == 'visible') ? 'hidden' : 'visible';
	// obj.style.visibility = 'visible';
}
function clrVisible(obj)
{
	obj = document.getElementById(obj);
	obj.style.visibility = 'hidden';
}
function placeIt(obj)
{
	obj = document.getElementById(obj);
	if (document.documentElement)
	{
		theLeft = document.documentElement.scrollLeft;
		theTop = document.documentElement.scrollTop;
	}
	else if (document.body)
	{
		theLeft = document.body.scrollLeft;
		theTop = document.body.scrollTop;
	}
	theLeft += popupDivx;
	theTop += popupDivy;
	obj.style.left = theLeft + 'px' ;
	obj.style.top = theTop + 'px' ;
	setTimeout("placeIt('layer1')",500);
}
// window.onscroll = setTimeout("placeIt('layer1')",500);

