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
            }/* elseif (!self::isProduct($obsah_stranky)) {
              printr('Produkt nenalezen ' . $url);
              $produkt = array('status' => 'Produkt nenalezen ', 'url' => $url);
              self::saveProduct($produkt, $soubor);
              } */ else {
                $produkt = array();
                $produkt = self::getName($obsah_stranky, $produkt);
                $produkt = self::getPopis($obsah_stranky, $produkt);
                $produkt = self::getParametr($obsah_stranky, $produkt);
                printr($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }

    private static function isProduct($obsah_stranky) {
        $tabulka = self::findFirst($obsah_stranky, 'table[class=sti_detail sti_detail_head]');
        foreach ($tabulka->find('tr') as $element) {
            if (self::findFirst($element, 'th')) {
                $prvek1 = self::findFirst($element, 'th')->plaintext;
            }
            if (self::findFirst($element, 'td')) {
                $prvek2 = self::findFirst($element, 'td')->plaintext;
            }
            if ($prvek1 == 'Kód' && $prvek2 != '') {
                return true;
            }
        }
        return false;
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

}
