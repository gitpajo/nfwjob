<?php

require 'ProductInterface.php';

abstract class ProductInfo implements ProductInterface {
    
    static function eraseFile($soubor) {
        if (file_exists($soubor)) {
            unlink($soubor);
        }
    }

    protected static function eraseSpace($promenna) {
        $promenna = str_replace(html_entity_decode('&nbsp;'), ' ', $promenna);
        $promenna = trim($promenna, ':');
        $promenna = str_replace('&nbsp;', '', $promenna);
        return trim($promenna);
    }
    
    protected static function findFirst($element, $selector) {
        return $element->find($selector, 0);
    }
    
    protected static function saveProduct($produkt, $soubor) {
        $obsah = print_r($produkt, true);
        return file_put_contents($soubor, $obsah, FILE_APPEND);
    }

}
