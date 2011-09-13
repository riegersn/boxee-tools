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
       die("Usage: fix_old_py.php file\n\n");
   }

   array_shift($argv);

   $wiki_file = $argv[0];
   //$path = 'xbmc/utils/';

//   $wiki = <<<ENDOFTEXT
//__TOC__
//<br/>
//
//===Overview===
//Boxee supports many "built-in functions" that can be called from within your application/skin. Some functions here are left over from XBMC and may no longer be relevant.
//
//
//===Example===
//<source line><onclick>notification(my title, my message)</onclick></source>
//<source line><onclick>ActivateWindow(0)</onclick></source>
//
//
//===Available Functions===
//{| class='boxee'
//!Function
//!Description
//|-
//%s|}
//ENDOFTEXT;

   $file = @file_get_contents($wiki_file);

   $format = "|%s\n";
   $format2 = "|<nowiki>%s</nowiki>\n";
   $wiki_builtins = '';

   $file = preg_replace("/^=.*?>(.*?)<.*\n:'''(.*?)'''\s(.*)\n:(.*)\n<.*?\">/im", "====$1====\n:<tt class='normal'>$3:$2</tt>\n:$4\n:<source lang=\"python\" line>", $file);
   $file = preg_replace("/\('''void'''\)/im", "()", $file);
   $file = preg_replace("/'''(.*?)'''\s(.*?)(\)|,)/im", "$2:$1$3", $file);

   $patterns = array(
      ':string',
      ':str',
      ':void',
      ':int',
      ':integer',
      ':bool',
      ':boolean',
      ':double'
   );

   $replace = array(
      ':String',
      ':String',
      ':Void',
      ':Integer',
      ':Integer',
      ':Boolean',
      ':Boolean',
      ':Float'
   );

   $file = str_replace($patterns, $replace, $file);

   //sometimes the overview section is not set up the same way. we need to run some more filters...
   $file = preg_replace("/^=*?<.*?>(.*?)<.*?$\n(^.*?$)\n<source.*?>(.*?)<.*?$/im", "===$1===\n:<tt class='normal'>$1()</tt>\n:$2\n:<source lang='python' line>$3</source>\n:<source line><onclick lang=\"python\"><![CDATA[\n$3\n]]></onclick></source>", $file);


   echo($file);
   //var_dump($x);

   exit();

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
