<?php // charset=ISO-8859-1
/*
 * galerie.php - a simple Fotos script
 * Copyright (C) 2002, 2003  Daniel Wacker <mail@wacker-welt.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * --
 * This script provides a simple Fotos of all images that are located
 * in the script's directory and subdirectories.
 *
 * Requirements
 * - PHP >= 4.1.0
 * - GD Library ( >= 2.0.1 for good thumbnails)
 * - JPEG software
 * - PHP >= 4.3.0 or GD < 1.6 for GIF support
 * - libpng for PNG support
 *
 * Installation
 * Simply put this script in a folder of your web server and call it in a
 * web browser. Be sure that the script has permission to read the image
 * files and to create and write into the thumbnail folder.
 *
 * Attention:
 * This script tries to generate jpeg thumbnail files in a subfolder of the
 * Fotos folder(s). The filenames look like "originalfilename.thumb.jpg".
 *
 * Integration with Lightbox 2.0 by http://www.esfera7.com
/* ------------------------------------------------------------------------- */

$captura = 'Galeria mecasohoy' ;

/* Select your language:
 * 'en' - English
 * 'de' - German
 */
$lang = 'en';

/* Select your charset
 */
$charset = 'ISO-8859-1';

/* How many images per page?
 */
$maxpics = 10;

/* Create thumbnails in this subfolder
 */
$thumbdir = 'thumbs';

/* Size of created thumbnails
 */
$thumbsize = 90;

/* Wether to show file names (true or false)
 */
$filenames = false;

/* Wether to show subdirectores (true or false)
 */
$subdirs = true;

/* Wether to show a title (true or false)
 */
$title = true;

/* Set the Fotos root relative to the script's directory.
 *
 * If you include() this script, set the path relative to
 * the directory of the script, that does the include().
 */
$picdir = '../../imagenes/';

/* Set this to true if you include() this script.
 */
$included = true;

/* Set this to true, if you include() this script and want the images
 * to be shown inline.
 */
$inline = false;

/* Set the thumbnail background color, if you include() this script.
 */
$bg = 'ffffff';

/* ------------------------------------------------------------------------- */
switch ($lang) {
case 'de':
 $words = array(
  'Fotos' => 'Galerie',
  'src' => 'Quelltext',
  'error' => 'Fehler',
  'php_error' => 'PHP Version 4.1 oder höher ist erforderlich.',
  'gd_error' => 'Die GD-Bibliothek wird benötigt. Siehe http://www.boutell.com/gd/.',
  'jpg_error' => 'Die JPEG-Bibliothek wird benötigt. Siehe ftp://ftp.uu.net/graphics/jpeg/.',
  'mkdir_error' => 'Schreibrecht im Verzeichnis ist erforderlich.',
  'opendir_error' => 'Das Verzeichnis "%1" kann nicht gelesen werden.'
 );
 break;
case 'en':
default:
 $words = array(
  'Fotos' => 'Fotos',
  'src' => 'source code',
  'error' => 'Error',
  'php_error' => 'PHP >= 4.1 is required.',
  'gd_error' => 'GD Library is required. See http://www.boutell.com/gd/.',
  'jpg_error' => 'JPEG software is required. See ftp://ftp.uu.net/graphics/jpeg/.',
  'mkdir_error' => 'Write permission is required in this folder.',
  'opendir_error' => 'The directory "%1" can not be read.'
 );
}
isset($_SERVER) || $error = error('php', array());
function_exists('imagecreate') || $error = error('gd', array());
function_exists('imagejpeg') || $error = error('jpg', array());
@ini_set('memory_limit', -1);
$jpg = '\.jpg$|\.jpeg$'; $gif = '\.gif$'; $png = '\.png$';
$fontsize = 2;
if (array_key_exists('src', $_REQUEST)) {
 ob_start();
 highlight_file(basename($_SERVER['PHP_SELF']));
 $src = ereg_replace('<font color="([^"]*)">', '<span>', ob_get_contents());
 $src = ereg_replace('</font>', '</span>', $src);
 ob_end_clean();
}
function w ($w) {
 global $words;
 return h($words[$w]);
}
function h ($w) {
 global $charset;
 return htmlentities($w, ENT_COMPAT, $charset);
}
function error ($w, $a) {
 return str_replace(array('%1'), $a, w($w .'_error'));
}

if (array_key_exists('dir', $_REQUEST) && $subdirs) $dir = $_REQUEST['dir'];
else $dir = '';
if (!empty($_SERVER['PATH_TRANSLATED'])) $d = dirname($_SERVER['PATH_TRANSLATED']);
elseif (!empty($_SERVER['SCRIPT_FILENAME'])) $d = dirname($_SERVER['SCRIPT_FILENAME']);
else $d = getcwd();
$delim = (substr($d, 1, 1) == ':') ? '\\' : '/';
$rp = function_exists('realpath');
if ($rp) $root = realpath($d . $delim . $picdir);
else $root = $d . $delim . $picdir;
if ($rp) $realdir = realpath($root . $dir);
else $realdir = $root . $dir;
if (substr($realdir, 0, strlen($root)) != $root) $realdir = $root;
$dirname = substr($realdir, strlen($root));
$dirnamehttp = $picdir . $dir;
if ($delim == '\\') $dirnamehttp = strtr($dirnamehttp, '\\', '/');
if (substr($dirnamehttp, 0, 2) == './') $dirnamehttp = substr($dirnamehttp, 2);
if (empty($dirnamehttp)) $dirnamehttp = '.';
if ($subdirs) {
 if (empty($dirname)) $ti = ''; else $ti = "$dirname";
} else $ti = '';
if (!$included) {
 if (isset($error)) echo("<title>$error</title>");
 else echo('<title>' . w('Fotos') . h($ti) . "</title>\n");
 echo("</head>\n<body>\n");
}
if (!($d = @opendir($realdir))) $error = error('opendir', array($realdir));
if (isset($error)) echo("<p style=\"color: red\">$error</p>\n");
else {
 if ($title) echo('');
 $dirs = $pics = array();
 $query = $jpg;
 if (function_exists('imagecreatefromgif')) $query .= "|$gif";
 if (function_exists('imagecreatefrompng')) $query .= "|$png";
 while (false !== ($filename = readdir($d))) {
  if ($filename == $thumbdir
   || ($filename == '..' && $dirname == '')
   || ($filename != '..' && substr($filename, 0, 1) == '.')) continue;
  $file = $realdir . $delim . $filename;
  if (is_dir($file)) $dirs[] = $filename;
  elseif (preg_match("/$query/i", $file)) $pics[] = $filename;
 }
 closedir($d);
 sort($dirs);
 sort($pics);
 $urlsuffix = '';
 foreach ($_GET as $v => $r) {
  if (!in_array($v, array('dir', 'pic', 'offset'))) $urlsuffix .= "&$v=" . urlencode($r);
 }
 if ($included && $inline && array_key_exists('pic', $_REQUEST)) {
  $pic = $_REQUEST['pic'];
  echo("<div id=\"picture\">\n");
  echo('<img src="' . h("$dirnamehttp/{$pics[$pic]}") . '" alt="' . h(basename($pics[$pic])) . '"');
  list($width, $height, $type, $attr) = @getimagesize($pic);
  if (!empty($width)) echo(" style=\"width: {$width}px; height: {$height}px\"");
  echo(" />\n");
  $url = ($dirname  == '') ? '?' : '?dir=' . urlencode($dirname) . '&';
  echo("");
  if ($pic > 0)
  echo('<a href="' . h($url) . 'pic=' . ($pic - 1) . h($urlsuffix) . '">[&lt;]</a> ');
  if ($pic >= $maxpics)
  $u = "{$url}offset=" . (floor($pic / $maxpics) * $maxpics) . $urlsuffix;
  else {
   if (array_key_exists('dir', $_REQUEST)) {
    $u = substr($url, 0, strlen($url) - 1) . $urlsuffix;
   } else {
    $u = preg_replace('/^([^?]+).*$', '\1/', $_SERVER['REQUEST_URI']);
    if (!empty($urlsuffix)) {
     if (strstr($u, '?') === false) $u .= '?' . substr($urlsuffix, 1);
     else $u .= $urlsuffix;
    }
   }
  }
  echo('<a href="' . h($u) . '">[-]</a>');
  if ($pic + 1 < sizeof($pics))
  echo(' <a href="' . h($url) . 'pic=' . ($pic + 1) . h($urlsuffix) . '">[&gt;]</a>');
  echo("\n</div>\n");
 } else {
  if (sizeof($dirs) > 0 && $subdirs) {
   echo("<ul id=\"directories\">\n");
   foreach ($dirs as $filename) {
    if ($rp) $target = substr(realpath($realdir . $delim . $filename), strlen($root));
    else $target = substr($realdir . $delim . $filename, strlen($root));
    if ($delim == '\\') $target = strtr($target, '\\', '/');
    if ($target == '') {
     $url = preg_replace("/^([^?]+).*$/i", "\1/web/pruebas/galeria.php", $_SERVER['REQUEST_URI']);
     if (!empty($urlsuffix)) {
      if (strstr($url, '?') === false) $url .= '?' . substr($urlsuffix, 1);
      else $url .= $urlsuffix;
     }
    } else $url = '?dir=' . urlencode($target) . $urlsuffix;
    echo('<p><a href="' . h($url) . '"  >' . h($filename) . "</a></p>\n");
   }
   echo("</ul>\n");
  }
  if (($num = sizeof($pics)) > 0) {
   if (array_key_exists('offset', $_REQUEST)) $offset = $_REQUEST['offset'];
   else $offset = 0;
   if ($num > $maxpics) {
    echo("<p id=\"pagenumbers\">\n Fotos: ");
    for ($i = 0; $i < $num; $i += $maxpics) {
     $e = $i + $maxpics - 1;
     if ($e > $num - 1) $e = $num - 1;
     if ($i != $e) $b = ($i + 1) . '-' . ($e + 1);
     else $b = $i + 1;
     if ($i == $offset) echo("<b>$b</b>");
     else {
      $url = ($dirname  == '') ? '?' : '?dir=' . urlencode($dirname) . '&amp;';
      echo("<a href=\"{$url}offset=$i" . h($urlsuffix) . "\">$b</a>");
     }
     if ($e != $num - 1) echo(' |');
     echo("\n");
    }
    echo("</p>\n");
   }
   echo("<p id=\"pictures\">\n");
   for ($i = $offset; $i < $offset + $maxpics; $i++) {
    if ($i >= $num) break;
    $filename = $pics[$i];
    $file = $realdir . $delim . $filename;
    if (!is_readable($file)) continue;
    if (!is_dir($realdir . $delim . $thumbdir)) {
     $u = umask(0);
     if (!@mkdir($realdir . $delim . $thumbdir, 0777)) {
      echo('<p style="color: red; text-align: center">' . w('mkdir_error') . '</span>');
      break;
     }
     umask($u);
    }
    $thumb = $realdir . $delim . $thumbdir . $delim . $filename . '.thumb.jpg';
    if (!is_file($thumb)) {
     if (preg_match("/$jpg/i", $file))
     $original = @imagecreatefromjpeg($file);
     elseif (preg_match("/$gif/i", $file))
     $original = @imagecreatefromgif($file);
     elseif (preg_match("/$png/i", $file))
     $original = @imagecreatefrompng($file);
     else continue;
     if ($original) {
      if (function_exists('getimagesize'))
      list($width, $height, $type, $attr) = getimagesize($file);
      else continue;
      if ($width > $height && $width > $thumbsize) {
       $smallwidth = $thumbsize;
       $smallheight = floor($height / ($width / $smallwidth));
       $ofx = 0; $ofy = floor(($thumbsize - $smallheight) / 2);
      } elseif ($width < $height && $height > $thumbsize) {
       $smallheight = $thumbsize;
       $smallwidth = floor($width / ($height / $smallheight));
       $ofx = floor(($thumbsize - $smallwidth) / 2); $ofy = 0;
      } else {
       $smallheight = $height;
       $smallwidth = $width;
       $ofx = floor(($thumbsize - $smallwidth) / 2);
       $ofy = floor(($thumbsize - $smallheight) / 2);
      }
     }
     if (function_exists('imagecreatetruecolor'))
$small = imagecreatetruecolor($thumbsize, $thumbsize);
     else $small = imagecreate($thumbsize, $thumbsize);
     sscanf($bg, "%2x%2x%2x", $red, $green, $blue);
     $b = imagecolorallocate($small, $red, $green, $blue);
     imagefill($small, 0, 0, $b);
     if ($original) {
      if (function_exists('imagecopyresampled'))
imagecopyresampled($small, $original, $ofx, $ofy, 0, 0, $smallwidth, $smallheight, $width, $height);
      else
imagecopyresized($small, $original, $ofx, $ofy, 0, 0, $smallwidth, $smallheight, $width, $height);
     } else {
      $black = imagecolorallocate($small, 0, 0, 0);
      $fw = imagefontwidth($fontsize);
      $fh = imagefontheight($fontsize);
      $htw = ($fw * strlen($filename)) / 2;
      $hts = $thumbsize / 2;
      imagestring($small, $fontsize, $hts - $htw, $hts - ($fh / 2), $filename, $black);
imagerectangle($small, $hts - $htw - $fw - 1, $hts - $fh, $hts + $htw + $fw - 1, $hts + $fh, $black);
     }
     imagejpeg($small, $thumb);
    }
    if ($filenames) echo('<div>');
    if ($included && $inline) {
    echo('<a href="?');
    if (array_key_exists('dir', $_REQUEST)) echo('dir=' . urlencode($_REQUEST['dir']) . '&amp;');
    echo('pic=' . $i . h($urlsuffix));
    } else echo('<a href="' . h("$dirnamehttp/$filename"));
    echo('" title="' . $captura . '" rel="lightbox"> <img src="' . h("$dirnamehttp/thumbs/$filename.thumb.jpg"));
    echo('" alt="' . h($filename) . '" style="');
    echo("width: {$thumbsize}px; height: {$thumbsize}px\" />");
    if ($filenames) echo('<p>' . h($filename) . '</p>');
    echo('</a>');
    if ($filenames) echo("</div>\n"); else echo("\n");
   }
   echo("</p>\n");
   if (!$included) {
    echo('<hr');
    if ($filenames) echo(' class="clear"');
    echo(" />\n");
   }
  }
 }
 if (!$included) echo('<p id="src"><a href="?src=true">' . w('src') . "</a></p>\n");
}
if (!$included) echo("</body>\n</html>");
?>
<a href="http://golondro.tk/web/pruebas/galeria.php"><img title="Volver a subir" width='62' height='25'src="../../imagenes/atras.jpg" alt="Volger a subir" align:right ></a> 
<a href="http://golondro.tk/"><img title="Volver a subir" width='62' height='25'src="../../imagenes/inicio.jpg" alt="Volger a subir" align:right ></a> 



<form action="leer.php" method="post" enctype="multipart/form-data">
    <b><input type="submit" value="Ver directorios" action="leer.php"> </b>
 
</form>


