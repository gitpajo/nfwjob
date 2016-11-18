<?php
class ProductInfoEReading extends ProductInfo {
    
    const SERVER_URL = 'http://www.ereading.cz/';
    
    static function getProduct($url, $soubor) {
        if (strpos($url, self::SERVER_URL) === FALSE) {
            print_r('URL neobsahuje doménu ' . self::SERVER_URL);
            $produkt = array('status' => 'Špatná doména ', 'url' => $url);
            self::saveProduct($produkt, $soubor);
        } else {
            $obsah_stranky = file_get_html($url);
            if ($obsah_stranky == false) {
                print_r('Nelze načíst stránku');
            } elseif (!self::isProduct($obsah_stranky)) {
                print_r('Produkt nenalezen ' . $url);
                $produkt = array('status' => 'Produkt nenalezen ', 'url' => $url);
                self::saveProduct($produkt, $soubor);
            } else {
                $produkt = array();
                $produkt = self::getName($obsah_stranky, $produkt);
                $produkt = self::getPopis($obsah_stranky, $produkt);
                print_r($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }
    
// Funkce pro vypsání názvu produktu
    private static function getName($obsah_stranky, $produkt) {
        $name = self::findFirst($obsah_stranky, 'div[class=f_left product_name]')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }
    
// Funkce pro vypsání popisu produktu
    private static function getPopis($obsah_stranky, $produkt) {
        $popis = self::findFirst($obsah_stranky, 'p[class=short_desc]')->plaintext;
        $produkt['popis'] = self::eraseSpace($popis);
        return $produkt;
    }
}
