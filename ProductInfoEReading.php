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
            } else {
                $produkt = array();
                $produkt = self::getBook($obsah_stranky, $produkt);
                print_r($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }

    private static function getBook($obsah_stranky, $produkt) {
        $divs = $obsah_stranky->find('div[class=bookListText]');
        foreach ($divs as $div) {
            $title = self::findFirst($div, 'h2')->plaintext;
            $author = self::findFirst($div, 'h3')->plaintext;
            $produkt[] = ['Title' => $title, 'Author' => $author];
        }
        return $produkt;
    }

}
