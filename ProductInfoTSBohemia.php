<?php

class ProductInfoTSBohemia {

    const SERVER_URL = 'http://dealer.tsbohemia.cz/';
    
    static function getProduct($url, $soubor) {

        $obsah_stranky = file_get_html($url);
        if ($obsah_stranky == false) {
            printr('Nelze načíst stránku');
        } elseif (!self::isProduct($obsah_stranky)) {
            printr('Produkt nenalezen '. $url);
            $produkt = array('status' => 'Produkt nenalezen ', 'url' => $url);
            self::saveProduct($produkt, $soubor);
        } else {
            $produkt = array();
            $produkt = self::getName($obsah_stranky, $produkt);
            $produkt = self::getInformation($obsah_stranky, $produkt);
            $produkt = self::getAvailability($obsah_stranky, $produkt);
            $produkt = self::getPopis($obsah_stranky, $produkt);
            $produkt = self::getParametr($obsah_stranky, $produkt);
            $produkt = self::getImage($obsah_stranky, $produkt);
            $produkt = self::getInclusion($obsah_stranky, $produkt);
            $produkt = self::unsetInformation($produkt);
            printr($produkt);
            self::saveProduct($produkt, $soubor);
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
    
    private static function renameInformation($prvek1) {
        $seznam = array(
            'Výrobce' => 'vyrobce',
            'Kód' => 'kod',
            'Part No.' => 'part_no',
            'Dostupnost na eshopu' => 'dostupnost_eshop',
            'Vaše cena bez DPH' => 'cena_bez_dph',
            'Recyklační poplatek  (RP*)' => 'recyklacni_poplatek',
            'Autorský fond  (AF*)' => 'autorsky_fond',
            'Vaše cena s RP*+ AF*' => 'cena_s_rp_af',
            'Vaše cena s DPH' => 'cena_s_rp_af',
            'Záruka spotřebitel' => 'zaruka_spotrebitel',
            'Záruka ostatní' => 'zaruka_ostatni',
            'Status' => 'status',
        );
        $hledej = array_keys($seznam);
        $nahrad = array_values($seznam);
        $prvek1 = str_replace(html_entity_decode('&nbsp;'), ' ', $prvek1);
        $prvek1 = str_replace($hledej, $nahrad, $prvek1);
        return $prvek1;
    }
    
    private static function unsetInformation($produkt) {
        foreach ($produkt as $klic => $value) {
            if ($klic == 'Dostupnost na pobočkách') {
                unset($produkt[$klic]);
            }
            if ($klic == 'Hodnocení produktu') {
                unset($produkt[$klic]);
            }
            if ($klic == 'Garance ceny') {
                unset($produkt[$klic]);
            }
            if ($klic == 'Mám o tento produkt zájem:') {
                unset($produkt['parametry'][$klic]);
            }
        }
        return $produkt;
    }
    
    private static function getName($obsah_stranky, $produkt) {
        $name = self::findFirst($obsah_stranky, 'div[class=f_left product_name]')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getInformation($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('table[class=sti_detail sti_detail_head]') as $tabulka) {
            foreach ($tabulka->find('tr') as $element) {

                $prvek1 = '';
                $prvek2 = '';
                if (self::findFirst($element, 'th')) {
                    $prvek1 = self::findFirst($element, 'th')->plaintext;
                    $prvek1 = self::renameInformation($prvek1);
                }
                if (self::findFirst($element, 'td')) {
                    $prvek2 = self::findFirst($element, 'td')->plaintext;
                    $prvek2 = trim($prvek2);
                }
                if ($prvek1 && $prvek2) {
                    $produkt[$prvek1] = $prvek2;
                }
            }
        }
        return $produkt;
    }

    private static function getAvailability($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('table[class=sti_detail_avail]') as $tabulka) {
            $i = 0;
            foreach ($tabulka->find('th') as $dostup) {
                $pobocka = $dostup->plaintext;
                if ($tabulka->find('img', $i)) {
                    $skladem = $tabulka->find('img', $i)->class;
                } else {
                    $skladem = '';
                }
                if ($skladem == 'img_skladem') {
                    $skladem = 1;
                } else {
                    $skladem = 0;
                }
                $i++;
                $produkt['dostupnost_pobocky'][$pobocka] = $skladem;
            }
        }
        return $produkt;
    }

    private static function getPopis($obsah_stranky, $produkt) {
        $popis = self::findFirst($obsah_stranky, 'div[id=popis-produktu]')->plaintext;
        $produkt['popis'] = self::eraseSpace($popis);
        return $produkt;
    }

    private static function getParametr($obsah_stranky, $produkt) {
         $div = self::findFirst($obsah_stranky, 'div[id=parametry]');
        foreach ($div->find('table[class=sti_details]') as $tabulka) {
            $podminka = false;
            foreach ($tabulka->find('tr') as $parametry) {
                if ($podminka == true) {
                    $klic = '';
                    $hodnota = '';
                    if (self::findFirst($tabulka, 'td')) {
                        $klic = self::findFirst($parametry, 'td')->plaintext;
                        $klic = self::eraseSpace($klic);
                    }
                    if ($tabulka->find('td', 1)) {
                        $hodnota = $parametry->find('td', 1)->plaintext;
                        $hodnota = self::eraseSpace($hodnota);
                    }
                    if ($klic && $hodnota) {
                        $produkt['parametry'][$klic] = $hodnota;
                    }
                }
                $podminka = true;
            }
        }
        return $produkt;
    }

    private static function getImage($obsah_stranky, $produkt) {
        $div = self::findFirst($obsah_stranky, 'div.sti_image');
        $i = 1;
        if ($div) {
            $images = $div->find('img');
            if ($images) {
                foreach ($images as $image) {
                    $produkt['gallery_url_image']['Image ' . $i] = self::SERVER_URL . $image->src;
                    $i++;
                }
            }
        }
        $divs = $obsah_stranky->find('div[class=sti_detail_gallery]');
        foreach ($divs as $div) {
            $produkt['gallery_url_image']['Image ' . $i] = self::SERVER_URL . self::findFirst($div, 'img')->src;
            $i++;
        }
        return $produkt;
    }

    private static function getInclusion($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('div[id=zarazeni-produktu]') as $tabulka) {
            $podminka = TRUE;
            $i = 0;
            $j = 0;
            $zarazeni = self::findFirst($tabulka, 'strong.hcat');
            while ($podminka && $zarazeni) {
                if ($zarazeni->next_sibling() != null) {
                    if ($zarazeni->tag == 'strong') {
                        $kategorie = $zarazeni->plaintext;
                        $produkt['zarazeni'][$kategorie] = '';
                    }
                    if ($zarazeni->tag == 'a') {
                        $vetev = self::eraseSpace($zarazeni->plaintext);
                        if ($produkt['zarazeni'][$kategorie] == '') {
                            $produkt['zarazeni'][$kategorie] .= $vetev;
                        } else {
                            $produkt['zarazeni'][$kategorie] .= ' -> ' . $vetev;
                        }
                    }
                    $zarazeni = $zarazeni->next_sibling();
                } else {
                    $podminka = FALSE;
                }
                $i++;
                $j++;
            }
        }
        return $produkt;
    }
}
