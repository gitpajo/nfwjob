<?php

class ProductInfoEil extends ProductInfo {

    const SERVER_URL = 'http://eil.com/';

// Řídídcí funkce třídy pro výpis jednotlivých produktů a volání privátních funkcí
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
                $produkt = self::getInformation($obsah_stranky, $produkt);
                $produkt = self::getImage($obsah_stranky, $produkt);
                print_r($produkt);
                self::saveProduct($produkt, $soubor);
            }
        }
    }

// Funkce pro zjištění, jestli produkt se zadaným url existuje  
    private static function isProduct($obsah_stranky) {
        $div = self::findFirst($obsah_stranky, 'div[itemtype=http://schema.org/Product]');
        if ($div) {
            return true;
        } else {
            return false;
        }
    }

// Funkce pro vypsání názvu produktu
    private static function getName($obsah_stranky, $produkt) {
        $name = self::findFirst($obsah_stranky, 'span[itemprop=name]')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }

// Funkce pro vypsání souvisejících umělců produktu 
    private static function getArtist($bunka, $produkt) {
        $artists = $bunka->next_sibling()->find('a');
        foreach ($artists as $artist) {
            $produkt['souvisejici_umelci'][] = self::eraseSpace($artist->plaintext);
        }
        return $produkt;
    }

// Funkce pro vypsání skladeb produktu   
    private static function getTrack($seznam_skladeb, $produkt) {
        unset($produkt['stopy']);
        $tracks = self::findFirst($seznam_skladeb, 'FONT')->innertext;
        $tracks_array = explode('<br>', $tracks);
        $cassette = '';
        foreach ($tracks_array as $one_track) {
            if (strpos($one_track, 'Cassette') === 0) {
                $cassette = $one_track;
            } else if (trim($one_track) === '') {
                unset($one_track);
            } else {
                if ($cassette === '') {
                    $produkt['stopy'][] = $one_track;
                } else {
                    $produkt['stopy'][$cassette][] = $one_track;
                }
            }
        }
        return $produkt;
    }

// Funkce pro vypsání url obrázků produktu    
    private static function getImage($obsah_stranky, $produkt) {
        $img = self::findFirst($obsah_stranky, 'img[itemprop=image]');
        if ($img) {
            $produkt['img'][] = trim(self::SERVER_URL, '/') . $img->src;
            $podminka = TRUE;
            while ($podminka) {
                if ($img->next_sibling() != NULL) {
                    $img = $img->next_sibling();
                    if ($img->src) {
                        $produkt['img'][] = trim(self::SERVER_URL, '/') . $img->src;
                    }
                } else {
                    $podminka = FALSE;
                }
            }
        } else {
            $produkt['img'] = 'nema obrazek';
        }
        return $produkt;
    }

// Funkce pro vypsání informací o produktu    
    private static function getInformation($obsah_stranky, $produkt) {
        $div = self::findFirst($obsah_stranky, 'div[itemtype=http://schema.org/Product]');
        $radky = $div->find('tr');
        $podminka = false;
        foreach ($radky as $radek) {
            if ($podminka == true) {
                $bunky = $radek->find('td');
                foreach ($bunky as $bunka) {
                    if ($bunka->plaintext == 'Tracklisting / Additional Info:') {
                        $produkt = self::getTrack($bunka->next_sibling(), $produkt);
                    } else if ($bunka->plaintext == 'Condition:') {
                        $produkt['stav'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Availability:') {
                        $dostupnost = self::eraseSpace($bunka->next_sibling()->plaintext);
                        $dostupnost = trim($dostupnost, 'In Stock - ');
                        $produkt['dostupnost'] = $dostupnost;
                    } else if ($bunka->plaintext == 'Year of Release:') {
                        $rok = self::eraseSpace($bunka->next_sibling()->plaintext);
                        $rok = substr($rok, 0, 4);
                        $produkt['rok'] = $rok;
                    } else if ($bunka->plaintext == 'Artist:') {
                        $umelec = self::eraseSpace($bunka->next_sibling()->plaintext);
                        $umelec = trim($umelec, 'click here for complete listing)');
                        $umelec = trim($umelec, '(');
                        $produkt['umelec'] = $umelec;
                    } else if ($bunka->plaintext == 'Title:') {
                        $dilo = self::eraseSpace($bunka->next_sibling()->next_sibling()->plaintext);
                        $dilo = trim($dilo, '(click here for more of the same title)');
                        $produkt['dilo'] = $dilo;
                    } else if ($bunka->plaintext == 'Price:') {
                        $cena = str_replace('change currency', '', $bunka->next_sibling()->plaintext);
                        $cena = self::eraseSpace($cena);
                        $produkt['cena'] = $cena;
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
