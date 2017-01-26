<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package   CodeIgniter
 * @author    Rick Ellis
 * @copyright Copyright (c) 2006, pMachine, Inc.
 * @license   http://www.codeignitor.com/user_guide/license.html
 * @link    http://www.codeigniter.com
 * @since   Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Code Igniter Asset Helpers
 *
 * @package   CodeIgniter
 * @subpackage  Helpers
 * @category  Helpers
 * @author    Nick Cernis | www.goburo.com
 * @author    Matias Montes | http://codeigniter.com/forums/member/39927/
 */

// ------------------------------------------------------------------------

 /**
  * Image tag helper
  *
  * Generates an img tag with a full base url to add images within your views.
  *
  * @access public
  * @param  string  the image name
  * @param  mixed   any attributes
  * @return string
  */

 function img_tag($image_name, $attributes = '')
 {
     if (is_array($attributes))
     {
       if (!isset($attributes['alt'])) $attributes['alt'] = '';
       $attributes = parse_tag_attributes($attributes);
     } elseif (is_string($attributes)) {
        $attributes = ' alt="' . $attributes . '"';
     }

     $obj =& get_instance();
     $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
     $img_folder = $obj->config->item('image_path');

     return '<img src="'.$base.$img_folder.$image_name.'"'.$attributes.' />';
 }

 function img_url($image_name = '') {
    $obj =& get_instance();
    $img_folder = $obj->config->item('image_path');
    $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
    return $base.$img_folder.$image_name;
  }

  function img_path($image_name = '') {
    $obj =& get_instance();
    $img_folder = $obj->config->item('image_path');
    $base = dirname(rtrim(BASEPATH,'/')).'/';
    return $base.$img_folder.$image_name;
  }

 // ------------------------------------------------------------------------

  /**
   * Stylesheets include helper
   *
   * Generates a link tag using the base url that points to an external stylesheet
   *
   * @access  public
   * @param    string the stylesheet name - leave the '.css' off
   * @param    mixed  any attributes
   * @return  string
   */

  function add_style($stylesheet, $forceRefresh = false, $attributes = '')
  { 
     $refreshParameter = ($forceRefresh) ? '?r='.rand(1,100) : '';

     if (is_array($attributes))
     {
       $attributes = parse_tag_attributes($attributes);
     }
     $obj =& get_instance();

     $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
     $style_folder = $obj->config->item('stylesheet_path');

       return '<link rel="stylesheet" type="text/css" href="'.$base.$style_folder.$stylesheet.'.css'.$refreshParameter.'"'.$attributes.' />'."\r\n";
  }

  function style_url($stylesheet = '') {
    $obj =& get_instance();
    $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
    $style_folder = $obj->config->item('stylesheet_path');
    $stylesheet = preg_match('/\.css$|^$/') ? $stylesheet : $stylesheet.'.css';
    return $base . $style_folder . $stylesheet;
  }

  function style_path($stylesheet = '') {
    $obj =& get_instance();
    $base = dirname(rtrim(BASEPATH,'/')).'/';
    $style_folder = $obj->config->item('stylesheet_path');
    $stylesheet = preg_match('/\.css$|^$/',$stylesheet) ? $stylesheet : $stylesheet.'.css';
    return $base . $style_folder . $stylesheet;
  }

// ------------------------------------------------------------------------

   /**
    * Javascript include helper
    *
    * Generates a link tag using the base url that points to external javascript
    *
    * @access public
    * @param  string  the javascript name - leave the '.js' off
    * @param  mixed   any attributes
    * @return string
    */

    function add_jscript($javascript, $forceRefresh = false, $attributes = '')
    {    
         $refreshParameter = ($forceRefresh) ? '?r='.rand(1,100) : '';

         if (is_array($attributes))
         {
           $attributes = parse_tag_attributes($attributes);
         }
         $obj =& get_instance();
       $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
       $jscript_folder = $obj->config->item('javascript_path');

         return '<script type="text/javascript" src="'.$base.$jscript_folder.$javascript.'.js'.$refreshParameter.'"'.$attributes.'></script>'."\r\n";
    }

    function jscript_url($javascript = '') {
      $obj =& get_instance();
      $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
      $jscript_folder = $obj->config->item('javascript_path');
      return $base . $jscript_folder . $javascript . '.js';
    }

    function jscript_path($javascript = '') {
      $obj =& get_instance();
      $base = dirname(rtrim(BASEPATH,'/')).'/';
      $jscript_folder = $obj->config->item('javascript_path');
      $javascript = preg_match('/\.js$|^$/',$javascript) ? $javascript : $javascript.'.js';
      return $base . $jscript_folder . $javascript ;
    }

 // ------------------------------------------------------------------------

 /**
  * Parse out the attributes
  *
  * Some of the functions use this
  * (duplicate from Rick Ellis' parse_url_attributes function in URL Helper.)
  *
  * @access private
  * @param  array
  * @param  bool
  * @return string
  */
 function parse_tag_attributes($attributes, $javascript = FALSE)
 {
  $att = '';
  foreach ($attributes as $key => $val)
  {
    if ($javascript == TRUE)
    {
      $att .= $key . '=' . $val . ',';
    }
    else
    {
      $att .= ' ' . $key . '="' . $val . '"';
    }
  }

  if ($javascript == TRUE)
  {
    $att = substr($att, 0, -1);
  }

  return $att;
 }

 // ------------------------------------------------------------------------

  /**
   * Favicon include helper
   *
   * Generates a link tag using the base url that points to favicon
   *
   * @access    public
   * @return    string
   */

  function add_favicon()
  {
      $obj =& get_instance();
        $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
        $img_folder = $obj->config->item('image_path');

        return '<link rel="shortcut icon" href="'.$base.$img_folder.'favicon.ico" />'."\r\n";
  }

  // ------------------------------------------------------------------------

  /**
   *  Flash include helper
   *
   * Generates an object tag and possibly an embed tag using the base url
   * that points to media.
   * $params must be an associative array which will be used to generate the
   * param tags needed
   *
   * @access    public
   * @param     string
   * @param     array
   * @param     string/array
   * @param     string
   * @return    string
   */
  function add_flash($flash, $params = array(), $attributes = '', $innerHTML = '')
  {

  if (is_array($attributes))
  {
    $attributes = parse_tag_attributes($attributes);
  }

  $obj =& get_instance();
  $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
  $media_folder = $obj->config->item("media_path");

  $tag  = "<object ";
  $tag .= "type=\"application/x-shockwave-flash\" ";
  $tag .= "data=\"{$base}{$media_folder}{$flash}.swf\" ";
  $tag .= $attributes;
  $tag .= ">";
  $tag .= "<param name=\"movie\" value=\"{$base}{$media_folder}{$flash}.swf\" />";

  foreach ($params as $k=>$v)
  {
    $tag .= "<param name=\"{$k}\" value=\"{$v}\" />";
  }

  $tag .= $innerHTML;

  $tag .= "</object>";

  return $tag;

  }

  // ------------------------------------------------------------------------

  function media_url($media = '') {
      $obj =& get_instance();
      $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
      $media_folder = $obj->config->item('media_path');
      return $base . $media_folder . $media;
    }

    function media_path($media = '') {
      $obj =& get_instance();
      $base = dirname(rtrim(BASEPATH,'/')).'/';
      $media_folder = $obj->config->item('media_path');
      return $base . $media_folder . $media ;
    }

  // ------------------------------------------------------------------------

  /**
   * Frameset include helper
   *
   * Generates a frame
   *
   * @access    public
   * @return    string
   */

  function add_frame($path,$name)
  {
        $obj =& get_instance();
        $base = $base = _is_secure() ? $obj->config->item('base_url') : $obj->config->item('base_url');
        $index_page = $obj->config->item('index_page');
        if (!empty($index_page)) $base .= $index_page."/";

        return '<frame src="'.$base.$path.'" name="'.$name.'"  scrolling="auto" noresize="noresize" />';
  }

  // ------------------------------------------------------------------------

  function _is_secure() {
  return isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on";
  }


$CI =& get_instance();
return $CI->config->load("assets");
?>