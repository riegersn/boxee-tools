<?

   /**
    * Generates wiki text for /Fonts on developer.boxee.tv
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
       die("Usage: fonts.php <fiona_trunk_location>\n\n");
   }

   array_shift($argv);

   $skin = $argv[0];
   $path = 'skin/boxee/720p';

   //echo "You have requested a $type room for $nights nights, checking in on $checkin. Thank you for your order!\n";

   $wiki = <<<ENDOFTEXT
<div class='notice'>Updated for Boxee version >= 1.0.<br/>Fonts have been changing frequently as we tweak the Boxee UI. This page will stay updated and be regenerated when/if they change again.</div>
__TOC__
<br/>

===Overview===
Ever since the Beta release of Boxee, we've supported multiple font types. Currently there are %d supported fonts, each with different styles to make up %d different font variations. Any control type that allows a label or text field and be assigned a specific font.


===Example===
<source line><control type='label' id='121'>
   <width>800</width>
   <height>60</height>
   <align>center</align>
   <font>%s</font>
   <label>This is the %s font!</label>
</control></source>


===Defaults===
If no font type/size is specified, the default is used. Defaults vary by control type.
{| class='boxee boxee-alt' style='width: 350px;'
!Control
!Default Font
|-
%s|}


===Font Chart===
{| class='boxee boxee-alt' style='width: 550px;'
!Name
!Font
!Size
!Line Spacing
!Style
|-
%s|}
ENDOFTEXT;


   $fonts = simplexml_load_file(unix_path($skin, $path, 'Font.xml'));
   $fonts = $fonts->fontset->font;

   $styles = count($fonts);
   $files = array();

   $format = "|%s\n";
   $wiki_font = '';
   $wiki_default = '';

   $example_font = $fonts[rand(0, $styles-1)];
   $example_font = $example_font->name;

   foreach($fonts as $font) {
      $wiki_font .= sprintf($format, $font->name);
      $wiki_font .= sprintf($format, str_replace('.ttf', '', $font->filename));
      $wiki_font .= sprintf($format, $font->size);
      $wiki_font .= sprintf($format, $font->linespacing);
      $wiki_font .= (isset($font->style)) ? sprintf($format, $font->style) : "|regular\n";
      $wiki_font .= "|-\n";

      $str = strtolower(trim($font->filename));
      if (!isset($files[$str]))
         $files[$str] = true;
   }

   $defaults = simplexml_load_file(unix_path($skin, $path, 'defaults.xml'));
   $defaults = $defaults->default;

   foreach($defaults as $default) {
      if (isset($default->font)) {
         $wiki_default .= sprintf($format, $default['type']);
         $wiki_default .= sprintf($format, $default->font);
         $wiki_default .= "|-\n";
      }
   }

   $total_fonts = count($files);
   $wiki = sprintf($wiki, $total_fonts, $styles, $example_font, $example_font, $wiki_default, $wiki_font);

   $date = date('M-d-Y', time());
   $wiki .= "\n\n<!-- auto-generated on $date -->\n";

   echo $wiki;

?>
