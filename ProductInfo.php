<?php

class ProductInfo {

    const SERVER_URL = 'http://dealer.tsbohemia.cz/';

    static function getProduct($url, $soubor) {

        $obsah_stranky = file_get_html($url);
        if ($obsah_stranky == false) {
            printr('Nelze načíst stránku');
        } elseif (!self::isProduct($obsah_stranky)) {
            printr('Produkt nenalezen '. $url);
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

    private static function saveProduct($produkt, $soubor) {
        $obsah = print_r($produkt, true);
        return file_put_contents($soubor, $obsah, FILE_APPEND);
    }
    
    private static function isProduct($obsah_stranky) {
        $tabulka =  $obsah_stranky->find('table[class=sti_detail sti_detail_head]', 0);
        foreach ($tabulka->find('tr') as $element) {
            if ($element->find('th', 0)) {
                $prvek1 = $element->find('th', 0)->plaintext;
            }
            if ($element->find('td', 0)) {
                $prvek2 = $element->find('td', 0)->plaintext;
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
    
    private static function eraseSpace($promenna) {
        $promenna = str_replace(html_entity_decode('&nbsp;'), ' ', $promenna);
        return trim($promenna);
    }
    
    private static function getName($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('div[class=f_left product_name]', 0)->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getInformation($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('table[class=sti_detail sti_detail_head]') as $tabulka) {
            foreach ($tabulka->find('tr') as $element) {

                $prvek1 = '';
                $prvek2 = '';
                if ($element->find('th', 0)) {
                    $prvek1 = $element->find('th', 0)->plaintext;
                    $prvek1 = self::renameInformation($prvek1);
                }
                if ($element->find('td', 0)) {
                    $prvek2 = $element->find('td', 0)->plaintext;
                    $prvek2 = trim($prvek2);
                }
                if ($prvek1 && $prvek2) {
                    $produkt[$prvek1] = $prvek2;
                }
                if ($prvek1 == 'Dostupnost na pobočkách') {
                    unset($produkt[$prvek1]);
                }
                if ($prvek1 == 'Hodnocení produktu') {
                    unset($produkt[$prvek1]);
                }
                if ($prvek1 == 'Objednat') {
                    unset($produkt[$prvek1]);
                }
                if ($prvek1 == 'Garance ceny') {
                    unset($produkt[$prvek1]);
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
        $popis = $obsah_stranky->find('div[id=popis-produktu]', 0)->plaintext;
        $produkt['popis'] = self::eraseSpace($popis);
        return $produkt;
    }

    private static function getParametr($obsah_stranky, $produkt) {
        $div = $obsah_stranky->find('div[id=parametry]', 0);
        foreach ($div->find('table[class=sti_details]') as $tabulka) {
            $podminka = false;
            foreach ($tabulka->find('tr') as $parametry) {
                if ($podminka == true) {
                    $klic = '';
                    $hodnota = '';
                    if ($tabulka->find('td', 0)) {
                        $klic = $parametry->find('td', 0)->plaintext;
                        $klic = self::eraseSpace($klic);
                    }
                    if ($tabulka->find('td', 1)) {
                        $hodnota = $parametry->find('td', 1)->plaintext;
                    }
                    if ($klic && $hodnota) {
                        $produkt['parametry'][$klic] = $hodnota;
                    }
                    if ($klic == 'Mám o tento produkt zájem:') {
                        unset($produkt['parametry'][$klic]);
                    }
                }
                $podminka = true;
            }
        }
        return $produkt;
    }

    private static function getImage($obsah_stranky, $produkt) {
        $div = $obsah_stranky->find('div.sti_image', 0);
        if ($div) {
            $images = $div->find('img');
            if ($images) {
                foreach ($images as $image) {
                    $produkt["url_image"] = self::SERVER_URL . $image->src;
                }
            }
        }
        return $produkt;
    }

   private static function getInclusion($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('div[id=zarazeni-produktu]') as $tabulka) {
            $podminka = TRUE;
            $i = 0;
            $j = 0;
            $zarazeni = $tabulka->find('strong.hcat', 0);
            while ($podminka && $zarazeni) {
                if ($zarazeni->next_sibling() != null) {
                    if ($zarazeni->tag == 'strong') {
                        $kategorie = $zarazeni->plaintext;
                        $kategorie = self::eraseSpace($zarazeni);
                        $produkt['zarazeni'][$kategorie] = '';
                    }
                    if ($zarazeni->tag == 'a') {
                        if ($produkt['zarazeni'][$kategorie] == '') {
                            $produkt['zarazeni'][$kategorie] .= $zarazeni->plaintext;
                        } else {
                            $produkt['zarazeni'][$kategorie] .= ' -> ' . $zarazeni->plaintext;
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
