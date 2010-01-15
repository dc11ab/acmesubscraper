<html>
<head>
<meta http-equiv="Content-Type" content="text/html">
<title>Acme Subscraper</title>

<style type= "text/css " media= "all ">
@import "acmestyle.css";
</style>

</head>
<body>
<h1>Acme Subscraper</h1>
<div style="width:700px;">
<fieldset>
<legend>Search results</legend>
<?php
// Get the input from the form
$title = trim($_POST['title']); // We trim of any blankspace fat from the form
$imdbID = trim($_POST['imdbID']); 
	// Lame validator to remove tt from imdb ID, if we get that from the form
	$pattern = '/^tt/';
	preg_match($pattern, $imdbID, $matches, PREG_OFFSET_CAPTURE);
	if ($matches) {
		$imdbID = substr($imdbID,2);
	}
$relsync = trim($_POST["relsync"]); 
$sublanguage = $_POST["sublanguage"];

// Say who we are to those we get content from
ini_set('user_agent', 'Acme Subscraper v0.0.1');

// Lame check to only start search if we have something to search for. 
// FIXME: Put more checks on the index page in the form, perhaps with some javascript,
// but I have my doubts it'll work A OK on the C200...
// At least we know there is something:
$checksum = strlen($title) + strlen($imdbID) + strlen($relsync);
if ($checksum > 1 ) {
	echo "<p>Searching for: ";

	// Now let's check what we got!
	if ($relsync != NULL) {
		echo " release \"<b>$relsync</b>\" ";
		}
	if ($title != NULL) {
		echo "title \"<b>$title</b>\" ";
		}
	if ($imdbID != NULL) {
		echo "imdb id \"<b>$imdbID</b>\" ";
		}
	if ($checksum > 1) {
		echo "and language: \"<b>$sublanguage<b>\" \n";
		}
	else { echo "</p>";}
	
	// checking we got SSO available
	echo "<p>Checking SSO availability...";
	if (checkhttpOK("http://www.subtitlesource.org")){
		echo " <i>SSO is available!</i></p>";
	}
	else { echo "</p><p><span style'color:red'>ERROR:</span> SSO site does not respond OK. Please check site availablity.</p>"; }
	// Checking if it's worth the effort to make the search
	if ($_POST["relsync"] != NULL) {
	$relresults = showresultsrelsync($relsync, $sublanguage);
	// And display the results
	print $relresults;
	}
	if ($_POST['imdbID'] != NULL) {
	$imdbresults = showresultsimdb($imdbID, $sublanguage);
	print $imdbresults;
	}
	if ($_POST['title'] != NULL) {
	$titleresults = showtitleresults($title, $sublanguage);
	print $titleresults;
	}
}
else { echo "<p>No search parameters given. Please return and make a new search!</p>"; }


//-------------------//
// imdb ID validator //
//-------------------//
// Not done yet...


//----------------//
// Title search	  //
//----------------//
function showtitleresults($title,$sublanguage) {
$num=0;
$uri = "http://www.subtitlesource.org/api/xmlsearch/$title/$sublanguage/0";
$xml = simplexml_load_file($uri);
$hits = count($xml->xmlsearch->sub);
if ($hits == 0 && $sublanguage !== "all") {
	$uri = "http://www.subtitlesource.org/api/xmlsearch/$title/all/0";
	$xml = simplexml_load_file($uri);
	$hits = count($xml->xmlsearch->sub);
}
echo "<p><h2>Results for <em>". $title."</em> title search</h2></p>\n";
if ($hits !== 0) {
	// traversing the results
	if ($sublanguage !== "all") {
		foreach ($xml->xmlsearch->sub as $sub) {
			if ($sublanguage == $sub->language) {
				echo "<div style=\"position; relative; clear:both;\"><form action=\"subextract.php\" method=\"post\" 
				style=\"background:grey;color:white;\">";
				echo "<img style=\"position:relative; float:right;\"src=\"http://www.subtitlesource.org/images/poster/".$xml->xmlsearch->sub[$num]->imdb.".jpg\">";
	        	echo "<p>Title: ".$sub->title ." (".$sub->year .")<br/>\n"; // title of movie
	        	echo "imdb: ".$sub->imdb .", SSO id: ".$sub->id . ", rid: ".$sub->rid ."<br />\n"; // imdb ID, SSO id = Subitle Source ID
					if ("$sub->season" !== "0") {
		        		echo "Season: ".$sub->season . " "; //season (0 if none) 
		        	}
		       		if ("$sub->episode" !== "0") {
		        		echo " Episode: ".$sub->episode . "<br />\n"; //episode (0 if none)
		       		}
		       		else { echo "<br />\n"; }
		        echo "Sync rel: ".$sub->releasename . "<br />\n"; //subtitle is synced for this release
		        echo "fps: ".$sub->fps . "<br />\n"; //frames per second of the video
		        echo "No#CD's: ".$sub->cd . "<br />\n"; //number of cds, e.g. number of subtitle files
	       		if ("$sub->hi" !== "0") {
	       			echo "<span id=\"hi\">Hearing impaired</span><br />\n"; //hearing impaired (1=true, 0=false)
	       		}
	       		echo "<input type=\"hidden\" value=\"$sub->id\" name=\"SSOid\" />";
	       		echo "<input type=\"hidden\" value=\"$sub->language\" name=\"language\" />";
	        	echo "<br/><input type=\"button\" value=\"Download to PC\" onClick=\"window.location='http://www.subtitlesource.org/download/zip/".$sub->id."'\">";
	        	echo "<input type=\"submit\" class=\"nmt\" value=\"Download to NMT\" /></p></form></div>";
		        $num++;
				}
			}
		} // End if $sublanguage !== "all" loop
	if ($sublanguage == "all") {
		foreach ($xml->xmlsearch->sub as $sub) {
				echo "<div style=\"position; relative; clear:both;\"><form action=\"subextract.php\" method=\"post\" 
				style=\"background:blue;color:white;\">";
				echo "<img style=\"position:relative; float:right;\"src=\"http://www.subtitlesource.org/images/poster/".$xml->xmlsearch->sub[$num]->imdb.".jpg\">";
		        echo "<p>Title: ".$sub->title . " (".$sub->year . ")<br />\n";
		        echo "imdb: ".$sub->imdb . ", SSO id: ".$sub->id . ", rid: ".$sub->rid . "<br />\n";
					if ("$sub->season" !== "0") {
		        		echo "Season: ".$sub->season . " "; //season (0 if none) 
		        	}
		       		if ("$sub->episode" !== "0") {
		        		echo " Episode: ".$sub->episode . "<br />\n"; //episode (0 if none)
		       		}
		       		else { echo "<br />\n"; }
		        echo "Sync rel: ".$sub->releasename . "<br />\n"; //subtitle is synced for this release
		        echo "fps: ".$sub->fps . "<br />\n"; //frames per second of the video
		        echo "No#CD's: ".$sub->cd . "<br />\n"; //number of cds
		        echo "Language: ".$sub->language . "<br />\n"; //subtitle language
		       if ("$sub->hi" !== "0") {
		        echo "<span id=\"hi\">Hearing impaired</span><br />\n"; //hearing impaired (1=true, 0=false)
		       		}
	       		echo "<input type=\"hidden\" value=\"$sub->id\" name=\"SSOid\" />";
	       		echo "<input type=\"hidden\" value=\"$sub->language\" name=\"language\" />";
	        	echo "<br/><input type=\"button\" value=\"Download to PC\" onClick=\"window.location='http://www.subtitlesource.org/download/zip/".$sub->id."'\">";
				echo " <input type=\"submit\" class=\"nmt\" value=\"Download to NMT\" /></p></form></div>";
		        $num++;
			}
		}
		
			if ($num == 0) { 
		echo "<p><em>No subtitles found in <span style=\"color:red;\">";
		if ($sublanguage == "all" && $hits ==  0) {
			echo "any language!</span> Please try another search.</em></p></div>";
		}
		else { 
		   	echo "$sublanguage</span>! Please try another search</p>";
		   	echo "<p>". ($hits-$num) . " subtitles found in ";
			// Let's show what other languages are available.
			$showem = foundlanguages($xml, $hits);
			echo $showem;
			}		 
		    echo "</em></p></div>";
		} 
		
} // End if $hits==0
else {
	echo "<div style=\"position:relative;clear:both;\" >";
	echo "<p><em>No subtitles found in <span style=\"color:red;\">$sublanguage or any other language!</span> Please try another search.</em></p></div>";
} 
} // End Title search function

//----------------//
// imdb ID search //
//----------------//
function showresultsimdb($imdbID,$sublanguage) {
 $uri = "http://www.subtitlesource.org/api/xmlsearch/$imdbID/imdb/0";
 $xml = simplexml_load_file($uri);
 $num=0;
 $hits = count($xml->xmlsearch->sub);
	echo "<p><h2>Results for " . $xml->xmlsearch->sub[0]->title . " - imdb ID search</h2></p>";
 if ($hits > 0) {
	echo "<div style=\"position:relative\"><img align=\"left\" src=\"http://www.subtitlesource.org/images/poster/" . $xml->xmlsearch->sub[0]->imdb . ".jpg\"</div>";
	echo "<div style=\"float:left;height:140px\"><p>Title: " . $xml->xmlsearch->sub[0]->title . " (" . $xml->xmlsearch->sub[0]->year . ") "; 
 	 if ($xml->xmlsearch->sub[0]->season != "0" && $xml->xmlsearch->sub[0]->episode != "0") { 
 	echo " season " . $xml->xmlsearch->sub[0]->season . ", episode " . $xml->xmlsearch->sub[0]->episode . "</p>"; 
	 }
	echo "<p><span id=\"imdb\"><a href=\"http://www.imdb.com/title/tt$imdbID\">http://www.imdb.com/title/tt$imdbID</a></span></div>"; 
		// Let's look for a specific language
		if ($sublanguage !== "all") {
				// traversing trough response
			foreach ($xml->xmlsearch->sub as $sub) {
				// filter out the requested language
				if ($sub->language == $sublanguage) {
					echo "<div style=\"position:relative;clear:both;\" ><form action=\"subextract.php\" method=\"post\" style=\"background:red; color:white;\"><p>";
		        	echo "SSO id: ".$sub->id . ", rid: ".$sub->rid . "<br />\n";
		        	echo "Sync rel: ".$sub->releasename . "<br />\n"; //subtitle is synced for this release
		        	echo "fps: ".$sub->fps . "<br />\n"; //frames per second of the video
		        	echo "No#CD's: ".$sub->cd . "<br />\n"; //number of cds
		       		if ("$sub->hi" !== "0") {
		        		echo "<span id=\"hi\">Hearing impaired</span><br />\n"; //hearing impaired (1=true, 0=false)
		       		}
		       		echo "<input type=\"hidden\" value=\"$sub->id\" name=\"SSOid\" />";
		       		echo "<br/><input type=\"hidden\" value=\"$sub->language\" name=\"language\" />";
	        	echo "<input type=\"button\" value=\"Download to PC\" onClick=\"window.location='http://www.subtitlesource.org/download/zip/".$sub->id . "'\">";
					echo "<input type=\"submit\" class=\"nmt\" class=\"nmt\" value=\"Download to NMT\" /></p></form></div>";
		       		$num++;
				}
			}
		} // End if $sublanguage !== "all" loop
		
		// Checking subs available in all languages
		if ($sublanguage == "all") {
			// in this case we show all subs available
			foreach ($xml->xmlsearch->sub as $sub) {
				echo "<div style=\"position:relative;clear:both;\" ><form action=\"subextract.php\" method=\"post\" style=\"background:red; color:white;\"><p>"; 
		        echo "SSO id: ".$sub->id . ", rid: ".$sub->rid . "<br />\n";
		        echo "Sync rel: ".$sub->releasename . "<br />\n"; //subtitle is synced for this release
		        echo "fps: ".$sub->fps . "<br />\n"; //frames per second of the video
		        echo "No#CD's: ".$sub->cd . "<br />\n"; //number of cds
		        echo "Language: ".$sub->language . "<br />\n"; //subtitle language
		       	if ("$sub->hi" !== "0") {
		        	echo "<span id=\"hi\">Hearing impaired</span><br />\n"; //hearing impaired (1=true, 0=false)
		       	}
		       	echo "<input type=\"hidden\" value=\"$sub->id\" name=\"SSOid\" />";
		       	echo "<input type=\"hidden\" value=\"$sub->language\" name=\"language\" />";
	        	echo "<br/><input type=\"button\" value=\"Download to PC\" onClick=\"window.location='http://www.subtitlesource.org/download/zip/".$sub->id."'\">";
				echo "<input type=\"submit\" class=\"nmt\" class=\"nmt\" value=\"Download to NMT\" /></p></form></div>";
		        $num++;
			}
 		} // End if $sublanguage == "all" loop

		echo "<div style=\"position:relative;clear:both;\" >";
		if ($num == 0) { 
			echo "<p><i>No subtitles found in <span style=\"color:red;\">";
			if ($sublanguage == "all" && $hits ==  0) {
			    echo "any language!</span> Please try another search.</i></p></div>";
			    }
		else { 
			   	echo "$sublanguage</span>! Please try another search</p>";
			   	echo "<p>". ($hits-$num) . " subtitles found in ";
				// Let's show what other languages are available.
				$showem = foundlanguages($xml, $hits);
				echo $showem."</i></p></div>"; 
			}
		}
		echo "</i></p></div>";
	} // End if hits = 0
 	else {
		echo "<div style=\"position:relative;clear:both;\">";
	 	echo "<p><i><span style=\"color:red;\">No subtitles found in any language!</span> Please try another search.";
		echo "</i></p></div>\n";
 	}
} // End function imdb search

//----------------//
// Release search //
//----------------//
function showresultsrelsync($relsync,$sublanguage) {
$uri = "http://www.subtitlesource.org/api/xmlsearch/$relsync/all/0";
$xml = simplexml_load_file($uri);
$num=0;
$hits = count($xml->xmlsearch->sub);
echo "<br><p><h2>Results for <i>". $relsync ."</i> release sync</h2></p>";
 if ($hits >0) {
	echo "<div style=\"position:relative\"><img align=\"left\" src=\"http://www.subtitlesource.org/images/poster/" . $xml->xmlsearch->sub[0]->imdb . ".jpg\"</div>";
	echo "<div style=\"float:left;height:140px\"><p>Title: " . $xml->xmlsearch->sub[0]->title . " (" . $xml->xmlsearch->sub[0]->year . ") "; 
 	 if ($xml->xmlsearch->sub[0]->season != "0" && $xml->xmlsearch->sub[0]->episode != "0") { 
 	echo " season " . $xml->xmlsearch->sub[0]->season . ", episode " . $xml->xmlsearch->sub[0]->episode . "</p>"; 
	 }
	echo "<p><span id=\"imdb\"><a href=\"http://www.imdb.com/title/tt" . $xml->xmlsearch->sub[0]->imdb . "\">http://www.imdb.com/title/tt" . $xml->xmlsearch->sub[0]->imdb . "</a></span></div>"; 
	
// Now let's show what subs we got for all languages
		if ($sublanguage == "all") {
			foreach ($xml->xmlsearch->sub as $sub) {
				echo "<div style=\"position:relativ;clear:both;\"><form action=\"subextract.php\" method=\"post\" style=\"background:grey; color:white;\>";
				echo "<p style=\"background:blue;\">";
			    echo "SSO id: ".$sub->id . ", rid: ".$sub->rid . "<br />\n";
			    echo "fps: ".$sub->fps . "<br />\n"; //frames per second of the video
			    echo "No#CD's: ".$sub->cd . "<br />\n"; //number of cds
			    echo "Language: ".$sub->language . "<br />\n"; //subtitle language
	       		if ("$sub->hi" !== "0") {
	        		echo "<span id=\"hi\">Hearing impaired</span><br />\n"; //hearing impaired (1=true, 0=false)
	       		}
			    echo "<input type=\"hidden\" value=\"$sub->id\" name=\"SSOid\" />";
	        	echo "<br/><input type=\"button\" value=\"Download to PC\" onClick=\"window.location='http://www.subtitlesource.org/download/zip/".$sub->id."'\">";
				echo "<input type=\"submit\" class=\"nmt\" value=\"Download to NMT\" /></p></form></div>";
		        $num++;
			}
		
		if ($num == "0") { 
			echo "<div style=\"position:relative;clear:both;\"><p><i>";
			echo "<span style=\"color:red;\">No subtitles found!</span> Please try another search.";
			echo "</i></p></div>";
			} 
		} // end if all subs
// Or let's check for a specific language
		if ($sublanguage !== "all") {
			foreach ($xml->xmlsearch->sub as $sub) {
				// filter out the requested language
				if ($sub->language == $sublanguage) {
					echo "<div style=\"clear:both;\"><form action=\"subextract.php\" method=\"post\" style=\"background:blue;\">";
					echo "<p>";
					echo "SSO id: ".$sub->id . ", rid: ".$sub->rid . "<br />\n";
					echo "fps: ".$sub->fps . "<br />\n"; //frames per second of the video
					echo "No#CD's: ".$sub->cd . "<br />\n"; //number of cds
					if ("$sub->hi" !== "0") {
						echo "<span id=\"hi\">Hearing impaired</span><br />\n"; //hearing impaired (1=true, 0=false)
			    	}
			        echo "<input type=\"hidden\" value=\"$relsync\" name=\"relsync\"/>";
			        echo "<input type=\"hidden\" value=\"$sublanguage\" name=\"language\"/>";
			        echo "<input type=\"hidden\" value=\"$sub->id\" name=\"SSOid\" />";
			        echo "<br/><input type=\"button\" value=\"Download to PC\" onClick=\"window.location='http://www.subtitlesource.org/download/zip/".$sub->id."'\">";
					echo "<input type=\"submit\" class=\"nmt\" value=\"Download to NMT\" /></p></form></div>\n";
					$num++;
				}
			}	
		} // end if !== "all" languages 

		echo "<div style=\"position:relative;clear:both;\" >";
		if ($num == 0) { 
			echo "<p><i>No subtitles found in <span style=\"color:red;\">";
			if ($sublanguage == "all" && $hits ==  0) {
			    echo "any language!</span> Please try another search.</i></p></div>";
			    }
			else { 
			   	echo "$sublanguage</span>! Please try another search</p>";
			   	echo "<p>". ($hits-$num) . " subtitles found in ";
				// Let's show what other languages are available.
				$showem = foundlanguages($xml, $hits);
				echo $showem;
			}		 
		}
		echo "</i></p></div>";
 } // End if hits = 0
 else {echo "<div><p><i>There was no hits in <span style='color:red;'>any language</span>! Please try another search.</i></p></div>"; }
} // End function showresultsrelsync


//--------------//
// List all available subtitle langages, comma separated.
//--------------//
function foundlanguages($xml, $hits) {
		// This is a stupid way to show them since array_unique() does not seem to work with the C200 PHP version
		// (e.g. on multidimensional arrays.)
		//unset($availablelanguages);
		for ($i = 0; $i <= $hits; $i++) {
			foreach ($xml->xmlsearch->sub[$i]->language as $sub => $lang) {
				$availablelanguages[] = $lang;
			}	
		}
		$availablelanguages = array_unique($availablelanguages);
		$comma_separated = implode(", ", $availablelanguages);
		return $comma_separated;
}

//-------------------------------------//
// Check if we've got error from SSO   //
//-------------------------------------//
function checkhttpOK($url) {
file_get_contents("$url");
if (in_array("HTTP/1.1 200 OK", $http_response_header)) {
    return TRUE;
}
else { return FALSE; }
// One of the errors at SSO: Access denied for user '420064_subtitlep'@'%' to database '420064_subtitlep'
}


//-------------------------------------//
// SSO only return 20 hits at the time //
//-------------------------------------//
function reiterateSSOsearch($url){
//$uri = "http://www.subtitlesource.org/api/xmlsearch/Star Trek/all/0";

// Here we insert a check and validate url
// if it's OK we check and output the $string needed to make up the full uri.


// SSO returns 20 hits at the time, starting at 0, hence the for loop
for ( $i = 0; $i > 60; $i += 20 ) { // how many times to iterate??? i=20 is up to 500 hits
	$url = $url . $i;
	$xml = simplexml_load_file($url);
	$hits = count($xml->xmlsearch->sub);
	while ($hits > 0) {
		foreach ($xml->xmlsearch->sub as $sub => $lang) {
		//read in the hits and place in a new array $xmlfull
		$xmlfull[] = $lang;
		//sleep(3);
		}
	}
	// continue; break; ??
} // End for loop
	return $xmlfull;
} // End function reiterateSSOsearch

//$test = "http://www.subtitlesource.org/api/xmlsearch/Trek/all/";
//$allresults = reiterateSSOsearch($test);
//var_dump($allresults);

echo "<br/><p><input type='button' value='Return to search' onClick=\"window.location='index.html';\" /></p>";

?>

</fieldset></div>
</body></html>