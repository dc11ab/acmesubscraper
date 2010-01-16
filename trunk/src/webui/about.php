<html><head><title>Acme Subscraper</title>
<style type="text/css" media="all">
@import "acmestyle.css ";
</style> 

<p class="about"></style>
</head><body style="background:black">
<h1>Acme Subscraper</h1>
<div style="width:700px;">
<fieldset>
<legend>About subscraper v0.0.1</legend>
<p class="about">This application downloads and extracts external subtitles files on the Popcorn Hour C-200. The scraper is optimized for use on this Syabas NMT platform.</p>
<p class="about"><span id="header">Dependencies</span></p><p class="about">The commands unzip and unrar are needed for extraction of subs.</p>
<p class="about"><span id="header">Usage</span><p class="about"><p class="about">Enter search parameters and choose individual or all subtitles. If you know the imdb ID (found in the imdb url) you can type in the digits.<br>Example:<br><tt>http://www.imdb.com/title/tt0361748/</tt> gives <tt>0361748</tt></p><p class="about">The scraper extracts the selected subtitle files in the<br>
<tt>/share/Download/Subtitles/</tt> folder on the local HDD.</p><p class="about">From there you may move it to any appropriate folder, and possibly rename it to match the name of the movie file.<br>Example: <tt>SomeMovieTitle.avi</tt> gives <tt>SomeMovieTitle.srt</tt></p>
<p class="about"><span id="header">Notes</span></p><p class="about">The first source for all languages is Subtitlesource.org, but for Swedish, the release name sync is also available from Undertexter.se.<p class="about">Remember that SSO only allows 100 subtitle downloads per 24h. If you fill that quota you will not get new subs from there until 24 hours have passed. The most common languages at SSO are: English, Swedish, Danish, Icelandic, Norwegian, Finnish, Spanish and French</p>
<p class="about"><span id="header">Sources</span>
<ul><li><a href="http://subtitlesource.org">Subtitlesource.org</a> (Multilanguage)</li>
<li><a href="http://undertexter.se">Undertexter.se</a> (Swedish only)</li>
</ul></p>
<p class="about"><span id="header">Credits</span></p>
<p class="about">Written 2010 by <a href="mailto:dc11ab+acmesub@gmail.com">dc11ab</a>, licensed under the <a href="http://sam.zoy.org/wtfpl/">WTFPL</a></p><p class="about">Thank's to Ger Teunis, lundman, PoPEye and others for inspiration to write this webapp.</p><p class="about">And a big thank you to all the translators out there!</p>
<p align="right"><input type="button" value="Return" onClick="window.location='index.html';" style="align:right" /></p>
</fieldset>

</body></html>

<!-- /* // 
// Wishlist : 
// ------------------------------------
// Add sources:
// Add Subsearch.org for searching? template="http://subsearch.org/{searchTerms}//3/0/0"
//		http://subsearch.org/Angels+and+demons/en/4/0/0
//		http://subsearch.org/Angels+and+demons/en/4/30/0 (next 30 entries)
//		http://subsearch.org/setlang.php?slang=en&page=search&sort=0&list=4&s=0&q=Angels and demons
// Add OpenSubtitles.org XML-RPC integration.
// Add country flags to the languages.
// Add multi download for seasons: http://www.subtitlesource.org/download/multi/0813715/3/swedish
// Add SSO scraping for more than 20 results: subtitlesource.org/api/xmlsearch/0361748/imdb/0 -> /imdb/21 -> /imdb/41 etc.
// Add validator for poster images (if they go missing?) and scaling.
// Add validator for sub downloads, one that actually checks the header prior to downloading.
// NMT renamer: check sub name is corresponding to releasename, e.g. remove ".eng." and other tags etc.
// Make a loop and check if file is available on source site A, if not try site B etc. or die with error message.
// PHP file manager function (this one requires quite a lot of work, perhaps better integrate with CSI FileManager?)
*/ --> 