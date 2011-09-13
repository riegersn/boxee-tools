<?

   /**
    * Generates wiki text for /Localization on developer.boxee.tv
    * @author Shawn Rieger <riegersn@boxee.tv>
    **/

   function unix_path()
   {
      $args = func_get_args();
      $paths = array();

      foreach( $args as $arg )
         $paths = array_merge( $paths, (array)$arg );

      foreach( $paths as &$path )
         $path = trim( $path, '/' );

      if( substr( $args[0], 0, 1 ) == '/' )
         $paths[0] = '/' . $paths[0];

      return join('/', $paths);
   }


   if ($argc != 2) {
       die("Usage: localization.php <fiona_trunk_location>\n\n");
   }

   array_shift($argv);

   $skin = $argv[0];
   $path = 'language/';

   //echo "You have requested a $type room for $nights nights, checking in on $checkin. Thank you for your order!\n";

   $wiki = <<<ENDOFTEXT
__TOC__
<br/>

===Overview===
The Boxee interface is localized for many different languages. Within the Boxee client are files named <tt>string.xml</tt>. Each in its own directory named after each supported language. Each <tt>string.xml</tt> defines all the text used in the Boxee UI. These localized strings can also be used in your [[Applications]] by utilizing the <tt>%s[]</tt> tag within your labels. See [[Label Formatting]] for more information.


===Supported Languages===
Currently Boxee supports %d different languages and %d localized strings.
%s

===Example===
The following example would display "Monday" in any of the supported languages depending on your current locale.
<source line><label>%s[11]</label></source>


===Localized Strings===
{| class='boxee' style='width:650px;'
!Number
!String Result
|-
%s|}
ENDOFTEXT;


   $langs = simplexml_load_file(unix_path($skin, $path, 'availablelangs.xml'));
   $langs = $langs->lang;

   $total_supported = count($langs);

   $format = "|%s\n";
   $format2 = "|<nowiki>%s</nowiki>\n";
   $wiki_supported = '';
   $wiki_strings = '';

   foreach($langs as $lang) {
      $wiki_supported .= "* " . $lang['dir_name'] . "\n";
   }

   $strings = simplexml_load_file(unix_path($skin, $path, 'English/strings.xml'));
   $strings = $strings->string;

   $strings_total = count($strings);

   foreach($strings as $string) {
      if (!trim((string) $string)) continue;
      $wiki_strings .= sprintf($format, $string['id']);
      if (substr_compare(trim($string), '-', 0, 1) == 0)
         $wiki_strings .= sprintf($format2, (string) $string);
      else
         $wiki_strings .= sprintf($format, (string) $string);
      $wiki_strings .= sprintf($format, '-');
   }

   //$total_fonts = count($files);
   $localize = '$LOCALIZE';
   $wiki = sprintf($wiki, $localize, $total_supported, $strings_total, $wiki_supported, $localize, $wiki_strings);

   $date = date('M-d-Y', time());
   $wiki .= "\n\n<!-- auto-generated on $date -->\n";

   echo $wiki;

?>
