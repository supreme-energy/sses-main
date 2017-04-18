// This function submits a predefined form that has only and action value. The ID
// of the form is 'br-action-call' and the ID of the action is 'br-action-call-a'.

function ssesGoTo(action)
{
    document.getElementById('sses-action-call-a').value = action;
    document.getElementById('sses-action-call').submit();
}

function ssesOpenOverlay(omesg,otitle)
{
    if(otitle === undefined) otitle = 'Express Industries Message';
    document.getElementById('sses-fade').style.display='block';
    document.getElementById('sses-overlay').style.display='block';
    document.getElementById('sses-overlay-body').innerHTML = omesg;
    document.getElementById('sses-overlay-top-title').innerHTML = otitle;
}

// This a convenient function for closing the generic overlay.

function ssesCloseOverlay()
{
    document.getElementById('sses-overlay').style.display='none';
    document.getElementById('sses-fade').style.display='none';
}
