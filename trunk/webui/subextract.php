<html>
<head><title>Acme Subscraper</title>
<style type= "text/css " media= "all ">
@import "acmestyle.css ";
</style> 
</head>

<body style="background:black">
<h1>Acme Subscraper</h1>
<div style="width:700px;">
<fieldset><legend>Download results</legend>
<?php
// Getting the subtitle SSO ID, release name and language from search results page (subsearch.php)
$id = $_POST["SSOid"];
$relsync = trim($_POST["relsync"]);
$language = $_POST["language"];

// The directory where we download and extract subs:
$subdir = "/share/Download/Subtitles";

//Check if our subdir exist, if not we create it
if (!is_dir($subdir)){ 
	// Note the this dir gets assigned with nobody:99 as user:group. 
	// Maybe the infamous "recursive chown nmt:nmt"-script fixes it upon reboot?
	exec("mkdir $subdir"); 
	if (is_dir($subdir)){
		echo "<p>Designated subtitle directory not found!</p><p><em>Created directory</em> $subdir</p>"; 
	}
	else { echo "<p>Failed to create $subdir - Please check permissions or create $subdir manually on your C-200</p>"; }
} 

// ----
// TO DO
// Here we should call a url validator function for:
// http://www.subtitlesource.org/download/zip/$id
// ----

// if the url is validated TRUE above then get the file:
getsub($id, $subdir);


//	else ... echo "<p>Could not find match at Subtitlesource.org</p>"; and:
$test = strlen($relsync);
if ($test > 0 && $language == "Swedish" ){
	echo "<br><p><span style='color:orange'>We got a match from Undertexter.se!</span></p><p>Attempting to download $relsync.rar to $subdir...</p>";
	getUndertexter($relsync, $subdir);
}

// show the directory of $subdir, aka /share/Download/Subtitles
//showdir($subdir);
$subdir = "/share/Download/Subtitles";
// List contents of $subdir folder
if (is_dir($subdir)) {
$directory_handle = @opendir("$subdir") or die("<p>Unable to open $subdir, aborting!</p>");
echo "<fieldset><legend>Directory list of ". $subdir."</legend>";
//running the while loop
while ($thefile = readdir($directory_handle)) {
	if($thefile!="." && $thefile!="..") {
   		echo "<a href='$subdir/$thefile'>$thefile</a><br/>"; // FIXME : This does not make a valid url.
	}
}
echo "</fieldset>";
//closing the directory
closedir($directory_handle);
}
else { echo "<p>ERROR: Could not get '$subdir' download directory</p>"; }
echo "<p>Tip: Move the subtitle file(s) to the movie directory with your remote control (File mode button) or use the CSI app \"FileManager\". If you use llink-c200 you can edit the llink.conf and point the subtitle redirect-folder to <tt>/share/Download/Subtitles</tt></p>";


//------------------------------------//
// FUNCTION TO DOWNLOAD SUBS FROM SSO //
//------------------------------------//
function getsub($id, $subdir) {

// SubtitleSource.org (SSO) uses format http://subtitlesource.org/download/zip/54884
// where 54884 is the SSO ID for the sub(s), in zipped format.

// Put together the download link
$ssolink = "http://www.subtitlesource.org/download/zip/$id";

//$funky = "/opt/sybhttpd/localhost.drives/SATA_DISK/Download/Subtitles/$id";

// Let's download the subfile
echo "<p>Downloading...</p>";
$data = file_get_contents("$ssolink");
file_put_contents("$subdir/$id.zip", $data);

$filename = "$subdir/$id.zip";
if (is_readable($filename)) {
    echo "<p><em>OK! File successfully downloaded to $subdir</em></p>"; // FIXME
} else {
    echo '<p><em>ERROR: The file is not available or corrupt.</em></p>';
}

// Checking the contents of the downloaded zip
exec("unzip -lq $subdir/$id.zip > $subdir/zip.txt");
exec("echo \n >> $subdir/zip.txt"); // Add a newline to make it easier to open the file for read(?)

// Check and list the contents in the zip
$myFile = "$subdir/zip.txt";
$handle = fopen ($myFile, 'r');
echo "<p>Extracting...</p><p><em>OK! Extracted:<br>";
while (!feof($handle)) {
    $buffer = fgets($handle, 1024); // original chunk was 4096
    if (file_exists(rtrim($myFile,"\n"))) {
        echo "<p><span='color:blue'>$buffer</span></p>";
    } 
    else { echo $buffer." has been removed."; break; }
}
echo "</em></p>";
fclose ($handle);


// Let's unzip the containing subtitle(s)
exec("unzip $subdir/$id.zip -d $subdir");

// And remove our temp files
exec("rm $subdir/$id.zip $subdir/zip.txt");

} // End Subtitlesource.org scraper function getsub()



//------------------------------//
// Swedish Undertexter scraping //  WHoooaa! This function needs a capital FIXME!
//------------------------------//
function getUndertexter ($relsync, $subdir) {
$releasename = $relsync;
$alfanum = strtoupper(substr("$releasename", 0, 1));    // returns first character???
$url = "http://s4.undertexter.se/undertext/".$alfanum."/".$releasename.".rar"; 

// FIXME : We should check that url exist
//	if ((some_url_validation_function($url)) {
		echo "<p><em>$releasename.rar exists!</em></p>";
//	}
//	else { echo "Undertexter.se match not found. Site may be unavailable."; break; }
		// Let's download the subfile
		echo "<p>Downloading...</p>";
		$subfile = file_get_contents($url);

//		// Check if our subdir exist, if not we create it
//		if (!is_dir($subdir)){ echo "<p>Creating directory $subdir </p>"; exec("mkdir $subdir"); } 

//		// And then save our sub on the NMT
		file_put_contents("$subdir/$releasename.rar", $subfile);

if (!is_file("$subdir/$releasename.rar")) {
	echo "<p>ERROR: Could not validate $subdir/$releasename.rar</p>";
}
else {
		echo "<p><em>OK! Downloaded: " . $releasename . ".rar</em></p>";
		
		// Let's check that we have unrar
		echo "<p>Checking for unrar...</p>";
		if (!is_readable("/share/Apps/LLink/unrar")) {
			echo "<p>The unrar utility was not found. Please install <a href='http://www.lundman.net/wiki/index.php/Llink'>llink</a>.<br>llink is available in the Community Software Installer (CSI): <a href='http://www.nmtinstaller.com'>NMTinstaller.com</a></p><p>The Acme Subscraper will now attempt to download the file but cannot unrar it.</p>";
			}
		else { echo "<p><em>OK! unrar found, proceeding!</em></p>"; }
		// Extract from RAR requires path to unrar 
		// Here we use a dependency to llink-c200
		//unrar <command> -<switch 1> -<switch N> <archive> <files...>
		exec("/share/Apps/LLink/unrar lb $subdir/$releasename.rar > $subdir/rar.txt");
		exec("echo \n >> $subdir/rar.txt");

		$myFile = "$subdir/rar.txt";
		$handle = fopen ($myFile, 'r');
		echo "<p>Extracting...</p><p><em>OK! Extracted:<br>";
		while (!feof($handle)) {
		    $buffer = fgets($handle, 1024); // original chunk was 4096
		    if (file_exists(rtrim($myFile,"\n"))) {
		        echo "<p><span='color:blue'>$buffer</span></p>";
		    } 
		    else { echo "<p>".$buffer." has been removed."; }
		}
		echo "</em></p>";
		fclose ($handle);			
		
		// exec($cmd . " > /dev/null &");
		exec("/share/Apps/LLink/unrar e -y $subdir/$releasename.rar $subdir > /dev/null &"); // unrar the subfile

// FIXME : Make this stuff undangerous.
//	if(is_file("$subdir/$releasename.rar")) {
			exec("rm -f $subdir/$releasename.rar $subdir/rar.txt > /dev/null &"); 
//	}


} // Else if downloaded file is readable
} // End function getUndertexter



// POSSIBLE FUTURE VALIDATOR TO CHECK HEAD OF FILES
// NOT USED YET
// I'm thinking to use it to check the subtitle downloads and the poster images.
// ...someday.
//function is_valid_url ( $url ){
//	$url = @parse_url($url);
//
//	if ( ! $url){
//		return false;
//	}
//
//	$url = array_map('trim', $url);
//	$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
//	$path = (isset($url['path'])) ? $url['path'] : '';
//
//	if ($path == ''){$path = '/';}
//
//	$path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';
//
//	if (isset($url['host']) AND $url['host'] != gethostbyname($url['host'])){
//		if ( PHP_VERSION >= 5 ){
//			$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
//		} else {
//			$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
//
//			if ( ! $fp ){
//				return false;
//			}
//
//			fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
//			$headers = fread ( $fp, 128 );
//			fclose ( $fp );
//		}
//		$headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
//		return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
//	}
//	return false;
//}




//----------------------//
/* FUNCTION TO DOWNLOAD */
//----------------------//
//function download($file_source, $file_target) {
//        $rh = fopen($file_source, "rb");
//        $wh = fopen($file_target, "wb");
//        if ($rh == FALSE || $wh == FALSE) {
//// error reading or opening file
//           return TRUE;
//        }
//        while (!feof($rh)) {
//            if (fwrite($wh, fread($rh, 1024)) == FALSE) {
//                   // 'Download error: Cannot write to file ('.$file_target.')';
//                   return TRUE;
//               }
//        }
//        fclose($rh);
//        fclose($wh);
//        // No error
//        return FALSE;
//}

// EOF PHP
?>

<br/>
<p align="right"><input type="button" value="Return to search" onClick="window.location='index.html';" style="align:right" /></p>
</fieldset></div>
</body>
<html>