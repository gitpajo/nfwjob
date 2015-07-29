<?php

class ProductInfoIce extends ProductInfo {

    const SERVER_URL = 'http://www.ice.com/';

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
                $produkt = self::getPrice($obsah_stranky, $produkt);
                $produkt = self::getPopis($obsah_stranky, $produkt);
                $produkt = self::getParametr($obsah_stranky, $produkt);
                $produkt = self::getImage($obsah_stranky, $produkt);
                printr($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }

    private static function isProduct($obsah_stranky) {
        $div = self::findFirst($obsah_stranky, 'div[itemprop=name]');
        if ($div) {
            return true;
        } else {
            return false;
        }
    }

// Funkce pro vypsání názvu produktu
    private static function getName($obsah_stranky, $produkt) {
        $name = self::findFirst($obsah_stranky, 'div[itemprop=name]')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getPopis($obsah_stranky, $produkt) {
        $popis = self::findFirst($obsah_stranky, 'div[id=details]')->plaintext;
        $produkt['popis'] = self::eraseSpace($popis);
        return $produkt;
    }

    private static function getParametr($obsah_stranky, $produkt) {
        $table = self::findfirst($obsah_stranky, 'table[class=data-table]');
        foreach ($table->find('tr') as $parametry) {
            $klic = '';
            $hodnota = '';
            if (self::findFirst($table, 'th')) {
                $klic = self::findFirst($parametry, 'th')->plaintext;
                $klic = self::eraseSpace($klic);
            }
            if (self::findFirst($table, 'td')) {
                $hodnota = self::findFirst($parametry, 'td')->plaintext;
                $hodnota = self::eraseSpace($hodnota);
            }
            if ($klic && $hodnota) {
                $produkt['parametry'][$klic] = $hodnota;
            }
        }

        return $produkt;
    }
    
    private static function getImage($obsah_stranky, $produkt) {
       $img = self::findFirst($obsah_stranky, 'a[class=highslide]');
       $produkt['obrazek'] = self::eraseSpace($img->href);
       return $produkt;
    }

    private static function getPrice($obsah_stranky, $produkt) {
        $div = self::findFirst($obsah_stranky, 'div[class=price-box]');
        $cena = self::findFirst($div, 'span[class=price]')->plaintext;
        $produkt['cena'] = self::eraseSpace($cena);
        $cena_bezna = self::findSecond($div, 'span[class=price]')->plaintext;
        $produkt['cena_bezna'] = self::eraseSpace($cena_bezna);
        return $produkt;
    }

}
