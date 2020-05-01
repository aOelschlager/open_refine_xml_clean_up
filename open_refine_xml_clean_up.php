/*
Script to clean up text file from open refine.
*/

<?php

    print "type a file path:  ";

    $dir = fgets(STDIN);
    $dir = trim($dir);

    if (!is_dir($dir)) {
        print "The directory $dir does not exist.\n";
        print "Exiting program.\n";

    } else {

        dirToArray($dir);
    }
        
    function dirToArray($dir) {
      $cdir = scandir($dir);

      $counter = 0;
      $print_array = array();

      foreach ($cdir as $key => $value) {

         if (!in_array($value,array(".",".."))) {

            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {

               $counter = 0;

               dirToArray($dir . DIRECTORY_SEPARATOR . $value);

            } else {
               $val_exten =  substr(strrchr($value, '.'), 1);
               $val_exten = strtolower($val_exten);

               if ($val_exten === "txt") {
                   $counter += 1;
                   write_file($value, $dir);
               }
            }
         }
      }
   }

   function write_file($value, $dir) {
      $delim = "*";
      $lines = file($value);
      $count = 1;

      $file_name = pathinfo($value, PATHINFO_FILENAME);
      $new_file =  $file_name . "_" . $count . ".xml";

      $file = fopen($new_file, 'w');

      foreach($lines as $line) {
         
         if (trim($delim) === trim($line)) {
            $count += 1;
            fclose($file);
            
            clean_xml_null($new_file);
            
            $file_name = pathinfo($value, PATHINFO_FILENAME);
            $new_file =  $file_name . "_" . $count . ".xml";
            $file = fopen($new_file, 'w');

         } else {
            $line = preg_replace('~(?<=>)(")|(")(?=<)~', '', $line);
            fwrite($file, $line);
         }
      }
      fclose($file);
   }
   
   function clean_xml_null($new_file) {
      
      $xml = simplexml_load_file($new_file, null, LIBXML_NOBLANKS);
      
      $remove = $xml->xpath("//mods:subject[mods:topic='null']");
      
      foreach ( $remove as $item ) {
         unset($item[0]);
      }

      $remove2 = $xml->xpath("//mods:note[text()='null']");

      foreach ( $remove2 as $item ) {
         unset($item[0]);
      }
      
      $domDocument = dom_import_simplexml($xml)->ownerDocument;
      $domDocument->formatOutput = true;
      $domDocument->save($new_file);
   }
?>
