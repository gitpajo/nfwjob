<?php

class ProductInfoEil {

    const SERVER_URL = 'http://eil.com/';

// Funkce pro výpis prvního selectoru v zadanem elementu
    private static function findFirst($element, $selector) {
        return $element->find($selector, 0);
    }

// Řídídcí funkce třídy pro výpis jednotlivých produktů a volání privátních funkcí
    static function getProduct($url, $soubor) {
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
            $produkt = self::getInformation($obsah_stranky, $produkt);
            printr($produkt);
            self::saveProduct($produkt, $soubor);
        }
    }

// Funkce pro uložení produktu do souboru
    private static function saveProduct($produkt, $soubor) {
        $obsah = print_r($produkt, true);
        return file_put_contents($soubor, $obsah);
    }

    private static function isProduct($obsah_stranky) {
        $div = $obsah_stranky->find('div[itemtype=http://schema.org/Product]', 0);
        if ($div) {
            return true;
        } else {
            return false;
        }
    }

// Funkce pro smazání přebytečných mezer
    private static function eraseSpace($promenna) {
        $promenna = str_replace(html_entity_decode('&nbsp;'), ' ', $promenna);
        $promenna = trim($promenna, ':');
        return trim($promenna);
    }

// Funkce pro vypsání názvu produktu
    private static function getName($obsah_stranky, $produkt) {
        $name = self::findFirst($obsah_stranky, 'span[itemprop=name]')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getInformation($obsah_stranky, $produkt) {
        $div = $obsah_stranky->find('div[itemtype=http://schema.org/Product]', 0);
        $radky = $div->find('tr');
        $podminka = false;
        foreach ($radky as $radek) {
            if ($podminka == true) {
                $bunky = $radek->find('td');
                foreach ($bunky as $bunka) {
                    if ($bunka->plaintext == 'Tracklisting / Additional Info:') {
                        $track_list = self::eraseSpace($bunka->next_sibling()->plaintext);
                        $track_list = str_replace("\r\n", '', $track_list);
                        $produkt['stopy'] = $track_list;
                    } else if ($bunka->plaintext == 'Condition:') {
                        $produkt['stav'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Availability:') {
                        $produkt['dostupnost'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Year of Release:') {
                        $produkt['rok'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Artist:') {
                        $produkt['umelec'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Title:') {
                        $produkt['dilo'] = self::eraseSpace($bunka->next_sibling()->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Price:') {
                        $produkt['cena'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Format:') {
                        $produkt['format'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Record Label:') {
                        $produkt['vydavatel'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Country of Origin:') {
                        $produkt['zeme_puvodu'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'EIL.COM Ref No:') {
                        $produkt['kod'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Related Artists:') {
                        $produkt = self::getArtist($bunka, $produkt);
                    }
                }
            }
            $podminka = true;
        }
        return $produkt;
    }

}
