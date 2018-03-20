<?php

//Determine if variable is set and is not NULL
if (isset($_GET['date'])) {
    $getDate = $_GET['date'];
    $getLocation = $_GET['location'];


//load required xml.
$xml = simplexml_load_file("$getLocation");

//Xpath. Source helped with this: https://devhints.io/xpath
//getting reading at 8am.
$result = $xml->xpath("//reading[@time='08:00:00' and contains(@date, '$getDate')]");



//https://stackoverflow.com/questions/18286735/building-a-google-chart-with-php-and-mysql
//create Multidimensional Array to hold column information
$rows = array();
$table = array();
$table["cols"] = array(
  array("label" => "date/time", "type" => "date"),
  array("label" => "NO2", "type" => "number"),
  array("role" => "style", "type" => "string" )
);



//set the date format
$dateFormat = "d/m/Y H:i:s";
//for each value in result from Xpath.

	# start ##################################################
foreach ($result as $single){

  // Interprets a string of XML into an object
  //Returns an object of class SimpleXMLElement with properties containing the data held within the xml document
	$reading = simplexml_load_string($single->asXML());


  //Parses a time string according to a specified format
  //parameters are: format(i.e:dateFormat), time(i.e: String representing the time.)
  //Returns a new DateTime instance or FALSE on failure.
  $date = DateTime::createFromFormat($dateFormat, ($reading->attributes()->date ." ". $reading->attributes()->time));
  $val = $reading->attributes()->val;

################################################################
//Format Date correct for google charts.

//U= Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
//Y= A full numeric representation of a year, 4 digits
//m = Numeric representation of a month, with leading zeros
//d= Day of the month, 2 digits with leading zeros
//H = 24-hour format of an hour with leading zeros
//i= Minutes with leading zeros
//s= Seconds, with leading zeros


//"Date(Year, Month, Day, Hours, Minutes, Seconds, Milliseconds)"
//When serializing data using the JavaScript DataTable object literal notation to build your DataTable,
//the new Date() constructor cannot be used. Instead, Google Charts provides a Date string representation that allows
//your date or datetime to be serialized and parsed properly when creating a DataTable. This Date string format simply
//drops the new keyword and wraps the remaining expression in quotation marks:
################################################################

  $dateFormatted = "Date(";
  $dateFormatted .= date("Y", $date->format("U")).", ";
  $dateFormatted .= (date("m", $date->format("U"))-1).", ";
  $dateFormatted .= date("d", $date->format("U")).", ";
  $dateFormatted .= date("H", $date->format("U")).", ";
  $dateFormatted .= date("i", $date->format("U")).", ";
  $dateFormatted .= date("s", $date->format("U")).")";


  //Syntax for associative arrays:
  //array(key=>value,key=>value,key=>value,etc.);
  $temp = array();
  $temp[] = array("v" => $dateFormatted); //add value
  $temp[] = array("v" => (int) ($val)); //add


  //https://stackoverflow.com/questions/31380320/change-point-colour-based-on-value-for-google-scatter-chart?rq=1
  //add colour to the points.
  $colour = colour($val);
  $temp[] = array("v" => $colour);


  $rows[] = array("c" => $temp); //add row to new column
}
	# finish ##################################################


$table["rows"] = $rows;
$finalTable = json_encode($table);
echo $finalTable;

}


######################################################################################################################
//colur function takes in value and decides on colour.
//Colours relate to The colour coding on the DEFRA Site
//returns a hexidecimal colour code string.


function colour($val) {
  if($val >= 0 && $val <=67) {
    return "#DAF7A6";
  }
  if($val >= 68 && $val <=134) {
    return "#80FF00";
  }
  if($val >= 135 && $val <=200) {
    return "#94C800";
  }
  if($val >= 201 && $val <=267) {
    return "#F3F000";
  }
  if($val >= 268 && $val <=334) {
    return "#FFC300";
  }
  if($val >= 335 && $val <=400) {
    return "#F19A00";
  }
  if($val >= 401 && $val <=467) {
    return "#FF5F5F";
  }
  if($val >= 468 && $val <=534) {
    return "#FE0404";
  }
  if($val >= 535 && $val <=600) {
    return "#900C3F";
  }
  if($val >= 601) {
    return "#BE02E3";
  }

}
######################################################################################################################


?>
