<?php

//Determine if variable is set and is not NULL
if (isset($_GET['date'])) {
    $getDate = $_GET['date'];
    $getLocation = $_GET['location'];
    $getTime = $_GET['time'];


//load required xml.
$xml = simplexml_load_file("$getLocation");



//get each part of the date
//Assign variables as if they were an array
list($year, $month, $day) = sscanf($getDate, "%d-%d-%d");

//get the day in integer value
$dayint = intval($day);

//get the next day for endDate
$nextDay = $dayint + 1;

$nextDayStr = strval($nextDay);

//make sure the day is valid if its single digit.
if($dayint < 10){
  $day = "0" . $day;
}
//make sure month is valid if its single digit.
$monthint = intval($month);

if($monthint < 10){
  $month = "0" . $month;
}

$startDate = "$day/$month/$year";
//create template to work for format with date method.
$tempdate = "$month/$day/$year";

//create next day date in format specified.
$endDate = date("d/m/Y", strtotime("+1 day", strtotime($tempdate)));



//Xpath. Source helped with this: https://devhints.io/xpath
# start date and time <= inputTime OR end date and time >= input time
# Basically big IF statement.
# translate will find : and replace it with ' ' so that it will then be seen as a number.

$result = $xml->xpath("//reading[(@date='$startDate' and translate(@time, ':', '') >= translate('$getTime', ':', '')) or"
        . "(@date='$endDate' and translate(@time, ':', '') <= translate('$getTime', ':', ''))]");





//need to sort array
//The comparison function must return an integer less than, equal to, or greater than zero if the first argument
//is considered to be respectively less than, equal to, or greater than the second.
usort($result, 'sortSimpleXMLElementByDateTime');



//https://stackoverflow.com/questions/18286735/building-a-google-chart-with-php-and-mysql
//create Multidimensional Array to hold column information
$rows = array();
$table = array();
$table["cols"] = array(
  array("label" => "date/time", "type" => "date"),
  array("label" => "NO2", "type" => "number")
);



//set the date format
$dateFormat = "d/m/Y H:i:s";


	# start ##################################################
  //for each value in result from Xpath.
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

$dateFormatted = "Date("
. date("Y", $date->format("U")).", "
.(date("m", $date->format("U"))-1).", "
. date("d", $date->format("U")).", "
. date("H", $date->format("U")).", "
. date("i", $date->format("U")).", "
. date("s", $date->format("U")).")";

  $temp = array();
  $temp[] = array("v" => $dateFormatted); //add value
  $temp[] = array("v" => (int) ($val)); //add value
  $rows[] = array("c" => $temp); //add row to new column
}
	# finish ##################################################


$table["rows"] = $rows;
$finalTable = json_encode($table);
echo $finalTable;






}

######################################################################################################################
//returns -1 if the first date is smaller than the second, 0 if equals and 1 if its bigger

function sortSimpleXMLElementByDateTime($a, $b) {
  $reading1 = simplexml_load_string($a->asXML());
  $reading2 = simplexml_load_string($b->asXML());

  //get date and time concatenated
  $dateFormat = "d/m/Y H:i:s";
  $date1 = DateTime::createFromFormat($dateFormat, ($reading1->attributes()->date . " " . $reading1->attributes()->time));
  $date2 = DateTime::createFromFormat($dateFormat, ($reading2->attributes()->date . " " . $reading2->attributes()->time));

  //return comparison
if($date1 == $date2){
    return 0;
  }
if($date1 < $date2){
    return -1;
  }
  else{
    return 1;
  }

}
######################################################################################################################



?>
