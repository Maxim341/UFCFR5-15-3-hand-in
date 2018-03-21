<?php
##############################################################################################################################################
//input and output documents
$inputarray = array("brislington.xml","fishponds.xml","newfoundland_way.xml","parson_st.xml","rupert_st.xml","wells_rd.xml");
$outputarray = array("brislington_no2.xml","fishponds_no2.xml","newfoundland_way_no2.xml", "parson_st_no2.xml","rupert_st_no2.xml","wells_rd_no2.xml");

##############################################################################################################################################



//loop through input producing required output.
for($i = 0; $i < sizeof($inputarray); $i++){


//Check to see if input file exists before attempting to read it.
if(!file_exists("input XML/".$inputarray[$i])) {
    echo "Input File ", $inputarray[$i], " does not exist! \n";
    exit(0);
  }

//initialize reader and writer and choose documents to read and write.
$reader = new XMLReader();
$writer = new XMLWriter();
$reader->open("input XML/".$inputarray[$i]);
$writer->openURI("output XML/".$outputarray[$i]);


//Write the header for the XML documents
$writer->startDocument('1.0','UTF-8');
$writer->setIndent(4);
$writer->startElement('data');
$writer->writeAttribute('type', 'nitrogen dioxide');


//represent the XML document; serves as the root of the document tree.
$doc = new DOMDocument;

//variables for getting correct info
$locationcheck = 0;




# start ##################################################

//loop through the document whilst we have a row
while ($reader->read())
{
// move to the first row node
  if ($reader->name == 'row') {

//Get a SimpleXMLElement object from a DOM node - Represents an element in an XML document.
$node = simplexml_import_dom($doc->importNode($reader->expand(), true));

//If it is the first time through the loop we need id, lat, long information
if ($locationcheck == 0) {

  $locationcheck = 1;
  //write required location data.
  $writer->startElement('location');
  $writer->writeAttribute('id', (string) $node->desc->attributes()->{'val'});
  $writer->writeAttribute('lat', (string) $node->lat->attributes()->{'val'});
  $writer->writeAttribute('long', (string) $node->long->attributes()->{'val'});


}

//check to see if the element exists because sometimes the elements dont exist.
if($node->date){

  //write required data to the reading tag
  $writer->startElement('reading');
  $writer->writeAttribute('date', (string) $node->date->attributes()->{'val'});
  $writer->writeAttribute('time', (string) $node->time->attributes()->{'val'});
  $writer->writeAttribute('val', (string) $node->no2->attributes()->{'val'});
  $writer->endElement();
}


  }
}

	# finish ##################################################
//end whole document
$writer->endDocument();
$writer->flush();
}
?>
