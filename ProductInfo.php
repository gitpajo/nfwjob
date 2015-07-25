<?php

class ProductInfo {

 private static function eraseSpace($promenna) {
        $promenna = str_replace(html_entity_decode('&nbsp;'), ' ', $promenna);
        $promenna = trim($promenna, ':');
        $promenna = str_replace('&nbsp;', '', $promenna);
        return trim($promenna);
    }
    
      private static function findFirst($element, $selector) {
        return $element->find($selector, 0);
    }
    
        private static function saveProduct($produkt, $soubor) {
        $obsah = print_r($produkt, true);
        return file_put_contents($soubor, $obsah);
    }
}
