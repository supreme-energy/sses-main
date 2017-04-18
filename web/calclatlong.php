
<!-- TWO STEPS TO INSTALL LATITUDE-LONGITUDE CONVERTER:

  1.  Copy the coding into the HEAD of your HTML document
  2.  Add the last code into the BODY of your HTML document  -->

<!-- STEP ONE: Paste this code into the HEAD of your HTML document  -->

<HEAD>

<script type="text/javascript">
<!--
/* This script and many more are available free online at
The JavaScript Source!! http://javascript.internet.com
Created by: Joe Ho */

function calc(Location) {
  // Retrieve the DD/DMS option
  for (var i=0;i<Location.inputType.length;i++) {
    if (Location.inputType[i].checked)
    {
      var inputed = Location.inputType[i].value;
    }
  }
  if (inputed == "DD")
  {
    // Retrieve Lat and Lon information
    var lat = Location.Latitude.value;
    var lon = Location.Longitude.value;
    if (lat==null)
    var lat=0;
    if (lon==null)
            var lon=0;

    // Check if any error occurred
    if (isNaN(Location.Latitude.value) || isNaN(Location.Longitude.value)) {
      alert("Latitude and Longitude must be numeric");
    } else if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
            alert("ERROR");
    } else {
      //Retrieve the latitude direction for Degrees Decimal
      for (var i=0;i<Location.LatDirect.length;i++) {
        if (Location.LatDirect[i].checked)
        {
          var LatDirect = Location.LatDirect[i].value;
        }
      }

      // If the user does not click direct button,
      // then a positive latitude value regards North, negative latitude value regards South
      if (LatDirect==null) {
        if (lat<0) {
          LatDirect = "S";
          Location.LatDirect[1].click();
        }
        else {
          LatDirect ="N";
          Location.LatDirect[0].click();
        }
      }

      // Retrieve the longitude direction for Deg/Min/Sec
      for (var i=0;i<Location.LonDirect.length;i++) {
        if (Location.LonDirect[i].checked)
        {
          var LonDirect = Location.LonDirect[i].value;
        }
      }

      // If the user does not click direct button,
      // then a positive latitude value regards East, negative latitude value regards West
      if (LonDirect==null) {
        if (lon<0) {
          LonDirect = "W";
          Location.LonDirect[1].click();
         }
        else {
          LonDirect ="E";
          Location.LonDirect[0].click();
        }
      }

      // Change to absolute value
      lat = Math.abs(lat);
      lon = Math.abs(lon);
      Location.Latitude.value=lat;
      Location.Longitude.value=lon;
      setAllEnabled(Location);

      // Convert to Degree Minutes Seconds Representation
      LatDeg = Math.floor(lat);
      LatMin = Math.floor((lat-LatDeg)*60);
      LatSec =  (Math.round((((lat - LatDeg) - (LatMin/60)) * 60 * 60) * 100) / 100 ) ;
      LonDeg = Math.floor(lon);
      LonMin = Math.floor((lon-LonDeg)*60);
      LonSec = (Math.round((((lon - LonDeg) - (LonMin / 60 )) * 60 * 60) * 100 ) / 100);

      // Copy result to the board
      Location.LatDeg.value=LatDeg;
      Location.LatMin.value=LatMin;
      Location.LatSec.value=LatSec;
      Location.LonDeg.value=LonDeg;
      Location.LonMin.value=LonMin;
      Location.LonSec.value=LonSec;
      if (LatDirect == "N") {
        Location.LatDMSDirect[0].click();
      } else {
        Location.LatDMSDirect[1].click();
      }
      if (LonDirect == "E") {
        Location.LonDMSDirect[0].click();
      } else {
        Location.LonDMSDirect[1].click();
      }
      clickedOption(Location);

      // Find the farthest Point Location
      farthestPoint(lat,lon,LatDeg,LonDeg,LatMin,LonMin,LatSec,LonSec,LatDirect,LonDirect);
    }
  } else if (inputed == "DMS") {

    // Retrieve Lat and Lon information
    var LatDeg = Location.LatDeg.value;
    var LatMin = Location.LatMin.value;
    var LatSec = Location.LatSec.value;
    var LonDeg = Location.LonDeg.value;
    var LonMin = Location.LonMin.value;
    var LonSec = Location.LonSec.value;

    // Assume the value to be zero if the user does not enter value
    if (LatDeg==null)
      LatDeg=0;
    if (LatMin==null) {
      LatMin=0;
    }
    if (LatSec==null) {
      LatSec=0;
    }
    if (LonDeg==null)
      LonDeg=0;
    if (LonMin==null) {
      LonMin=0
    }
    if (LonSec==null){
      LonSec=0;
    }

    // Check if any error occurred
    if (isNaN(Location.LatDeg.value) || isNaN(Location.LonDeg.value) || isNaN(Location.LatMin.value) || isNaN(Location.LonMin.value) || isNaN(Location.LatSec.value) || isNaN(Location.LonSec.value)) {
      alert("Latitude and Longitude must be numeric");
    } else if (LatDeg != Math.round(LatDeg) || LonDeg != Math.round(LonDeg) || LatMin != Math.round(LatMin) || LonMin != Math.round(LonMin)) {
      alert("ERROR");
    } else if (LatDeg < -90 || LatDeg > 90 || LonDeg < -180 || LonDeg > 180 || LatMin < -60 || LatMin > 60 || LonMin < -60 || LonMin > 60 || LatSec < -60 || LatSec > 60 || LonSec < -60 || LonSec > 60) {
      alert("ERROR");
    } else {
    // If no error, then go on

    // Retrieve the latitude direction for Degrees Decimal
    for (var i=0;i<Location.LatDMSDirect.length;i++) {
      if (Location.LatDMSDirect[i].checked)
      {
        var LatDMSDirect = Location.LatDMSDirect[i].value;
      }
    }

    // If the user does not click direct button,
    // then a postive latitude value regards North, negative latitude value regards South
    if (LatDMSDirect==null) {
      if (LatDeg<0 || Location.LatDeg.value=="-0") {
        LatDMSDirect = "S";
        Location.LatDMSDirect[1].click();
      }
      else {
        LatDMSDirect ="N";
        Location.LatDMSDirect[0].click();
      }
    }

    // Retrieve the longitude direction for Deg/Min/Sec
    for (var i=0;i<Location.LonDMSDirect.length;i++) {
      if (Location.LonDMSDirect[i].checked)
      {
        var LonDMSDirect = Location.LonDMSDirect[i].value;
      }
    }

    // If the user does not click direct button,
    // then a positive latitude value regards East, negative latitude value regards West
    if (LonDMSDirect==null) {
      if (LonDeg<0 || Location.LonDeg.value=="-0") {
        LonDMSDirect = "W";
        Location.LonDMSDirect[1].click();
      } else {
        LonDMSDirect ="E";
        Location.LonDMSDirect[0].click();
      }
    }

    // Change to absolute value
    LatDeg = Math.abs(LatDeg);
    LonDeg = Math.abs(LonDeg);
    LatMin = Math.abs(LatMin);
    LonMin = Math.abs(LonMin);
    LatSec = Math.abs(LatSec);
    LonSec = Math.abs(LonSec);
    setAllEnabled(Location);

    // Convert to Decimal Degrees Representation
    var lat = LatDeg + (LatMin/60) + (LatSec / 60 / 60);
    var lon = LonDeg + (LonMin/60) + (LonSec / 60 / 60);
    if ( lat <= 90 && lon <= 180 && lat >=0 && lon >= 0 )
    {
      // Copy the absolute value to the board
      Location.LatDeg.value=LatDeg;
      Location.LonDeg.value=LonDeg;
      Location.LatMin.value=LatMin;
      Location.LonMin.value=LonMin;
      Location.LatSec.value=LatSec;
      Location.LonSec.value=LonSec;

      // Rounding off
      lat = (Math.round(lat*1000000)/1000000);
      lon = (Math.round(lon*1000000)/1000000);

      // Copy result to the board
      Location.Latitude.value=lat;
      Location.Longitude.value=lon;
      if (LatDMSDirect == "N") {
        Location.LatDirect[0].click();
      } else {
        Location.LatDirect[1].click();
      }
      if (LonDMSDirect == "E") {
        Location.LonDirect[0].click();
      } else {
        Location.LonDirect[1].click();
      }
        clickedOption(Location);
        farthestPoint(lat,lon,LatDeg,LonDeg,LatMin,LonMin,LatSec,LonSec,LatDMSDirect,LonDMSDirect);
      } else
        alert("ERROR!!");
    }
  }
}

function clickedOption(Location) {
        // Retrieve The DD/DMS Option
        for (var i=0;i<Location.inputType.length;i++) {
                if (Location.inputType[i].checked)
                {
                        var inputed = Location.inputType[i].value;
                }
        }
        changeOption(Location,inputed);
}

function resetForm(Location) {
        changeOption(Location,"DD");
}

function changeOption(Location,inputed) {
  if (inputed=="DD") {
    with (Location) {
      LatDeg.disabled=true;
      LonDeg.disabled=true;
      LatMin.disabled=true;
      LonMin.disabled=true;
      LatSec.disabled=true;
      LonSec.disabled=true;
      Latitude.disabled=false;
      Longitude.disabled=false;
      LatDirect[0].disabled=false;
      LonDirect[0].disabled=false;
      LatDirect[1].disabled=false;
      LonDirect[1].disabled=false;
      LatDMSDirect[0].disabled=true;
      LonDMSDirect[0].disabled=true;
      LatDMSDirect[1].disabled=true;
      LonDMSDirect[1].disabled=true;
    }
  } else if (inputed =="DMS") {
    with (Location){
      Latitude.disabled=true;
      Longitude.disabled=true;
      LatDeg.disabled=false;
      LonDeg.disabled=false;
      LatMin.disabled=false;
      LonMin.disabled=false;
      LatSec.disabled=false;
      LonSec.disabled=false;
      LatDMSDirect[0].disabled=false;
      LonDMSDirect[0].disabled=false;
      LatDMSDirect[1].disabled=false;
      LonDMSDirect[1].disabled=false;
      LatDirect[0].disabled=true;
      LonDirect[0].disabled=true;
      LatDirect[1].disabled=true;
      LonDirect[1].disabled=true;
    }
  }
}

function setAllEnabled(Location) {
  with (Location) {
    Latitude.disabled=false;
    Longitude.disabled=false;
    LatDeg.disabled=false;
    LonDeg.disabled=false;
    LatMin.disabled=false;
    LonMin.disabled=false;
    LatSec.disabled=false;
    LonSec.disabled=false;
    LatDMSDirect[0].disabled=false;
    LonDMSDirect[0].disabled=false;
    LatDMSDirect[1].disabled=false;
    LonDMSDirect[1].disabled=false;
    LatDirect[0].disabled=false;
    LonDirect[0].disabled=false;
    LatDirect[1].disabled=false;
    LonDirect[1].disabled=false;
  }
}

function farthestPoint(lat,lon,LatDeg,LonDeg,LatMin,LonMin,LatSec,LonSec,LatDirect,LonDirect) {
  var farLat,farLon,farLat2,farLon2,farLatDeg,farLonDeg;
  var farLatMin,farLonMin,farLatSec,farLonSec,farLatDirect,farLonDirect;
  var dist;
  if (LatDirect == "N") {
    farLatDirect = "S";
  } else {
    farLatDirect = "N";
  }
  if (LonDirect == "E") {
    farLonDirect = "W";
  } else {
    farLonDirect = "E";
  }
  farLat = lat;
  farLatDeg = LatDeg;
  farLatMin = LatMin;
  farLatSec = LatSec;
  farLon = 180 - Math.abs(lon);

  // Method 1
  farLonDeg = Math.floor(farLon);
  farLonMin = Math.floor((farLon-farLonDeg)*60);
  farLonSec = (Math.round((((farLon - farLonDeg) - (farLonMin / 60 )) * 60 * 60) * 100 ) / 100);

  // Method 2
  /*
    farLonSec = 60 - LonSec;
    if (farLonSec == 60) {
      farLonSec=0;
    }
    if (farLonSec >0) {
      farLonMin = 60 - LonMin - 1;
    } else {
      farLonMin = 60 - LonMin ;
    }
    if (farLonMin ==60) {
      farLonMin =0;
    }
    if (farLonMin >0) {
      farLonDeg = 180 - Math.abs(farLonDeg) - 1;
    } else {
      farLonDeg = 180 - Math.abs(farLonDeg);
    }
  */
  farLon = Math.round(farLon*1000000)/1000000;
  farLonSec = Math.round(farLonSec*100)/100;
  elev = 200;
  farElev = 200;
  var dist = distance(lat,LatDirect,lon,LonDirect,elev,farLat,farLatDirect,farLon,farLonDirect,farElev);
  var alertMsg="";
  alertMsg+=("The location you entered is : " + "\n\n" +
  "Latitude: " + lat + " " + LatDirect + "\n" + "Degrees: " + LatDeg + " Minutes: " + LatMin + " Seconds: " + LatSec + " " + LatDirect + "\n\n" +
  "Longitude: " + lon + " " + LonDirect + "\n" + "Degrees: " + LonDeg + " Minutes: " + LonMin + " Seconds: " + LonSec + " " + LonDirect + "\n\n" +
  "---------------------------------------------------------------" + "\n");

  alertMsg+=("The farthest location from the point you entered is : " + "\n\n" +
  "Latitude: " + farLat + " " + farLatDirect + "\n" + "Degrees: " + farLatDeg + " Minutes: " + farLatMin + " Seconds: " + farLatSec + " " + farLatDirect + "\n\n" +
  "Longitude: " + farLon + " " + farLonDirect + "\n" + "Degrees: " + farLonDeg + " Minutes: " + farLonMin + " Seconds: " + farLonSec + " " + farLonDirect + "\n\n");

  alertMsg+=("---------------------------------------------------------------" + "\n");
  alertMsg+=("Distance: " + dist + " km ");

  alert(alertMsg);
}

function distance(lat,LatDirect,lon,LonDirect,elev,lat2,ptDirect,lon2,ptLonDirect,ptElev) {
  var radLat,radLon,radLat2,radLon2;
  if (ptDirect == "S")
    lat2 = lat2*-1;
  if (ptLonDirect == "W")
    lon2 = lon2*-1;
  if (LatDirect == "S")
    lat = lat*-1;
  if (LonDirect == "W")
    lon = lon*-1;
  var radian = 180/Math.PI;
  radLat=lat/radian;
  radLon=lon/radian;
  radLat2=lat2/radian;
  radLon2=lon2/radian;
  var dist = calcDistance(radLat,radLon,elev,radLat2,radLon2,ptElev);
  dist = Math.round(dist*100)/100;
  return dist;
}


// calculate the distance between two coordinates
function calcDistance(lat1,long1,elev,lat2,long2,ptElev) {
  var earthEquatorialRadius = 6378.14; // in kilometers
  var earthFlattened = 1/298.257;
  var exCentricityEarth = 0.08181922;
  var radian = 180/Math.PI;
  meanPosLat = (lat1+lat2)/2;
  meanNegLat = (lat1-lat2)/2;
  lambda = (long1-long2)/2;
  with(Math) {
    si = pow(sin(meanNegLat)*cos(lambda),2)+pow(cos(meanPosLat)*sin(lambda),2);
    co = pow(cos(meanNegLat)*cos(lambda),2)+pow(sin(meanPosLat)*sin(lambda),2);
    delta = atan(sqrt(si/co));
    ra = sqrt(si*co)/delta;
    di = 2*delta*earthEquatorialRadius;
    ha =(3*ra-1)/2/co;
    hb =(3*ra+1)/2/si;
    distances = di*(1+earthFlattened*ha*pow(sin(meanPosLat)*cos(meanNegLat),2)-earthFlattened*hb*pow(cos(meanPosLat)*sin(meanNegLat),2));
  }

  // Taking elevation into consideration
  var totalElev, elevDist
  totalElevDist = Math.abs(elev - ptElev);

  // Convert back to km
  totalElevDist = totalElevDist / 1000;
  elevDist = Math.sqrt((distances * distances) + (totalElevDist * totalElevDist));
  return elevDist;
}
// -->
</script>
</HEAD>

<!-- STEP TWO: Copy this code into the BODY of your HTML document  -->

<BODY>

<div id="demo">
<form name="Locations">
<input class="script" type="radio" name="inputType" value="DD" checked onclick="clickedOption(this.form)"> <strong>Degrees Decimal</strong>

<br>Latitude   : <input class="script" name="Latitude" type="text" size="5">  North <input class="script" type="radio" name="LatDirect" value="N"> South <input class="script" type="radio" name="LatDirect" value="S">
<br>Longitude: <input class="script" name="Longitude" type="text" size="5">  East  <input class="script" type="radio" name="LonDirect" value="E"> West  <input class="script" type="radio" name="LonDirect" value="W">
<br>

<br><input class="script" type="radio" name="inputType" value="DMS" onclick="clickedOption(this.form)"> <strong>Degrees, Minutes, & Seconds</strong>
<br><strong>Latitude:</strong>
<br>Degrees: <input class="script" name="LatDeg" type="text" size="5" disabled>  Minutes: <input class="script" name="LatMin" type="text" size="5" disabled>  Seconds: <input class="script" name="LatSec" type="text" size="5" disabled>
<br>  North <input class="script" type="radio" name="LatDMSDirect" value="N" disabled>  South <input class="script" type="radio" name="LatDMSDirect" value="S" disabled>
<br><strong>Longitude:</strong>
<br>Degrees: <input class="script" name="LonDeg" type="text" size="5" disabled>  Minutes: <input class="script" name="LonMin" type="text" size="5" disabled>  Seconds: <input class="script" name="LonSec" type="text" size="5" disabled>
<br>  East  <input class="script" type="radio" name="LonDMSDirect" value="E" disabled>    West <input class="script" type="radio" name="LonDMSDirect" value="W" disabled>

<br><br><input class="script" value="Calculate" type="button" onclick="calc(this.form)">
<input class="script" name="Reset" type="reset" value="Clear" onclick="resetForm(this.form)">
</form>
</div>

<p><center>
<font face="arial, helvetica" size"-2">Free JavaScripts provided<br>
by <a href="http://javascriptsource.com">The JavaScript Source</a></font>
</center><p>

<!-- Script Size:  16.48 KB -->

