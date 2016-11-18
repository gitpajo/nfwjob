<?php

class ProductInfoBook extends ProductInfo {

    const SERVER_URL = 'http://katalog-carmen.knihovnabbb.cz/';

// Řídídcí funkce třídy pro výpis jednotlivých produktů a volání privátních funkcí
    static function getProduct($url, $soubor) {
        if (strpos($url, self::SERVER_URL) === FALSE) {
            print_r('URL neobsahuje doménu '.self::SERVER_URL);
            $produkt = array('status' => 'Špatná doména ', 'url' => $url);
            self::saveProduct($produkt, $soubor);
        } else {
            $obsah_stranky = file_get_html($url, FALSE, self::getContext());
            if ($obsah_stranky == false) {
                print_r('Nelze načíst stránku');
            } elseif (!self::isProduct($obsah_stranky)) {
                print_r('Produkt nenalezen '.$url);
                $produkt = array('status' => 'Produkt nenalezen ', 'url' => $url);
                self::saveProduct($produkt, $soubor);
            } else {
                $produkt = array();
                $produkt = self::getBook($obsah_stranky, $produkt);
                print_r($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }

    // Funkce pro zjištění, jestli produkt se zadaným url existuje  
    private static function isProduct($obsah_stranky) {
        $div = self::findFirst($obsah_stranky, 'div[class=showitems]');
        if ($div) {
            return true;
        } else {
            return false;
        }
    }

    protected static function saveProduct($produkt, $soubor) {
        foreach ($produkt as $pro) {
            if (isset($pro['Title'])) {
                file_put_contents($soubor, $pro['Title'].' '.$pro['Author'].PHP_EOL, FILE_APPEND);
            }
        }
    }

    private static function getBook($obsah_stranky, $produkt) {
        $divs = $obsah_stranky->find('div[class=showitems]');
        foreach ($divs as $div) {
            $title = self::findFirst($div, 'div[class=title]')->plaintext;
            $author = self::findFirst($div, 'div[class=author]')->plaintext;
            $produkt[] = ['Author' => $author, 'Title' => $title];
        }
        return $produkt;
    }

    private static function getContext() {
        $opts = array(
            'http' => array(
                'user_agent' => 'My agent name',
            )
        );
        return stream_context_create($opts);
    }

}
