<?php

class ProductInfoBook extends ProductInfo {
      
      const SERVER_URL = 'http://katalog-carmen.knihovnabbb.cz/';
      
// Řídídcí funkce třídy pro výpis jednotlivých produktů a volání privátních funkcí
    static function getProduct($url, $soubor) {
        if (strpos($url, self::SERVER_URL) === FALSE) {
            printr('URL neobsahuje doménu ' . self::SERVER_URL);
            $produkt = array('status' => 'Špatná doména ', 'url' => $url);
            self::saveProduct($produkt, $soubor);
        } else {
            $obsah_stranky = file_get_html($url);
            if ($obsah_stranky == false) {
                printr('Nelze načíst stránku');
            } elseif (!self::isProduct($obsah_stranky)) {
                printr('Produkt nenalezen ' . $url);
                $produkt = array('status' => 'Produkt nenalezen ', 'url' => $url);
                self::saveProduct($produkt, $soubor);
            } else {
                $produkt = array();
                $produkt = self::getName($obsah_stranky, $produkt);
                $produkt = self::getAuthor($obsah_stranky, $produkt);
                printr($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }
    
    private static function getName($obsah_stranky, $produkt) {
        $divs = $obsah_stranky->find('div[class=title]');
        foreach ($divs as $div) {
            $produkt[]['Title '] = $div->plaintext;
        }
        return $produkt;
    }
    
    private static function getAuthor($obsah_stranky, $produkt) {
        $divs = $obsah_stranky->find('div[class=author]');
        foreach ($divs as $div) {
            $produkt[]['Author '] = $div->plaintext;
        }
        return $produkt;
    }
}
