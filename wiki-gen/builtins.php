<?

   /**
    * Generates wiki text for /Builtins on developer.boxee.tv
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
       die("Usage: builtins.php <fiona_trunk_location>\n\n");
   }

   array_shift($argv);

   $skin = $argv[0];
   $path = 'xbmc/utils/';

   $wiki = <<<ENDOFTEXT
__TOC__
<br/>

===Overview===
Boxee supports many "built-in functions" that can be called from within your application/skin. Some functions here are left over from XBMC and may no longer be relevant.


===Example===
<source line><onclick>notification(my title, my message)</onclick></source>
<source line><onclick>ActivateWindow(0)</onclick></source>


===Available Functions===
{| class='boxee'
!Function
!Description
|-
%s|}
ENDOFTEXT;

   $file = @file_get_contents(unix_path($skin, $path, 'Builtins.cpp'));

   $format = "|%s\n";
   $format2 = "|<nowiki>%s</nowiki>\n";
   $wiki_builtins = '';

   $builtins = array();
   preg_match_all('/^.*?\{."(.*?)",.*?(true|false),.*?"(.*?)".*?\},(.*?)$/im', $file, $matches);

   foreach($matches[1] as $key => $value)
      $builtins[] = array($value, $matches[3][$key], trim($matches[4][$key], ' /,'));

   foreach($builtins as $b) {
      $wiki_builtins .= sprintf($format, $b[0]);
      $extra = ($b[2]) ? " - Example: <tt>".$b[2]."</tt>" : '';
      $wiki_builtins .= sprintf($format, $b[1] . $extra);
      $wiki_builtins .= sprintf($format, '-');
   }

   $wiki = sprintf($wiki, $wiki_builtins);

   $date = date('M-d-Y', time());
   $wiki .= "\n\n<!-- auto-generated on $date -->\n";

   echo $wiki;

?>
