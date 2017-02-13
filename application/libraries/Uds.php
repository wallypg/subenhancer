<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Uds {
  public function __construct()
  {
      $this->_CI =& get_instance();
      $this->_CI->load->library("Ocr");
  }

  public function fixUds($string)
  { 

    $stringArray = $this->_CI->ocr->ocrCheck($string,'ddd',false);
    $string = $stringArray['ocredLine'];

    if(!preg_match("/(?:(?:.*\n)?.*TUSUBTITULO.*(?:\n.*)?)|(?:-.*\n-.*)/i", $string)) {

      $splittedLine = explode("\n", $string);

      if(count($splittedLine) == 2) {

        $charsFirstLine = mb_strlen($splittedLine[0]);
        $charsSecondLine = mb_strlen($splittedLine[1]);

        if ($charsFirstLine + $charsSecondLine < 40) {
          $string = preg_replace("/\n/", " ", $string);
          $nl = 0;
        } else {
          $fix = 0;
          $testUds = 1;
          for($i=1; $i<=15 && $fix==0; $i++) {
            $pattern1 = "/(^.*[?!,\.])(\s)(.{1,".$i."})(\n)(.{1,".(40-$i)."}$)/";
            $pattern2 = "/(^.{1,".(40-$i)."})(\n)(.{1,".$i."}.*[?!,\.])(\s)(.+)/";
            if(preg_match_all($pattern1, $string, $matches)){
              $fix = 1;
              $testUds = 0;
              $string = preg_replace_callback(
                $pattern1,
                function ($matches) {
                  return $matches[1]."\n".$matches[3]." ".$matches[5];
                },
                $string
              );
            } elseif(preg_match_all($pattern2, $string, $matches)) {
              $fix = 1;
              $testUds = 0;
              $string = preg_replace_callback(
                $pattern2,
                function ($matches) {
                  return $matches[1]." ".$matches[3]."\n".$matches[5];
                },
                $string
              );
            }
          }

          $division = $charsSecondLine/$charsFirstLine;                 
          if($testUds && $division > 2.8) {
            
            // $pattern = "/(^.{1,10}\n(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos)(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos))?\s.{2,15}?)(\s)(.{20,40}$)/";
            // $pattern = "/((?:^.{1,10}|\w{1,15})\n(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos)(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos))?(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has\sestado|han\sestado|he\sestado|hemos\sestado|habías\sestado|había\sestado|habían\sestado|habíamos\sestado|has|ho|han|he|hemos|habías|había|habían|habíamos))?\s(?:iba a ir|[\w\.,]{3,16}))(\s)(.{10,40}$)/u";
            $pattern = "/((?:^.{1,10}|\w{1,15})\n(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos)(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos))?(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos))?(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has\sestado|han\sestado|he\sestado|hemos\sestado|habías\sestado|había\sestado|habían\sestado|habíamos\sestado|has|ho|han|he|hemos|habías|había|habían|habíamos))?\s(?:iba a ir|[\w\.,]{3,16}))(\s)(.{10,40}?$)/u";
            if(preg_match_all($pattern, $string, $matches)){
              $string = preg_replace_callback(
                $pattern,
                function ($matches) {
                  return $matches[1]."\n".$matches[3];
                },
                $string
              );
              $string = preg_replace("/\n/", " ", $string, 1);
            }
          }
        }
      }
    }
    return $string;
  }
}