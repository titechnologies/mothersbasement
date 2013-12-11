<?php


class Varien_Data_Form_Element_Wyomindnotificationmanagerextensionslist extends Varien_Data_Form_Element_Multiselect {

    public function XML2Array($xml) {
        $newArray = array();
        $array = (array) $xml;
        foreach ($array as $key => $value) {
            $value = (array) $value;
            if (isset($value [0])) {
                $newArray [$key] = trim($value [0]);
            } else {
                $newArray [$key] = $this->XML2Array($value, true);
            }
        }
        return $newArray;
    }
    
    public function getValues() {
        $dir = "app/code/local/Wyomind/";
        $ret = array();
        if (is_dir($dir)) {
            if (($dh = opendir($dir)) != false) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($dir.$file) && $file != "." && $file != "..") {
                        
                        $xml = simplexml_load_file($dir.$file.'/etc/system.xml');
                        $namespace = strtolower($file);
                        $label = $this->XML2Array($xml);
                        $label = $label['sections'][$namespace]['label'];
                        
                        $ret[] = array('label'=>$label,'value'=>$file);
                    }
                }
                closedir($dh);
            }
        }
        return $ret;
    }
    

}