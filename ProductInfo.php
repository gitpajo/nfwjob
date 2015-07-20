<?php

class ProductInfo {

    const SERVER_URL = 'http://dealer.tsbohemia.cz/';

    static function getProduct($url) {

        $obsah_stranky = file_get_html($url);
        if ($obsah_stranky == false) {
            printr('Nelze načíst stránku');
        } else {
            $produkt = array();
            $produkt = getInformation($obsah_stranky, $produkt);
            $produkt = getAvailability($obsah_stranky, $produkt);
            $produkt = getPopis($obsah_stranky, $produkt);
            $produkt = getParametr($obsah_stranky, $produkt);
            $produkt = getImage($obsah_stranky, $produkt);
            $produkt = getInclusion($obsah_stranky, $produkt);
            printr($produkt);
            saveProduct($produkt, 'produkty.txt');
        }
    }

    function saveProduct($produkt, $soubor) {
        $obsah = print_r($produkt, true);
        return file_put_contents($soubor, $obsah, FILE_APPEND);
    }

    function getInformation($obsah_stranky, $produkt) {
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
        foreach ($obsah_stranky->find('table[class=sti_detail sti_detail_head]') as $tabulka) {
            foreach ($tabulka->find('tr') as $element) {

                $prvek1 = '';
                $prvek2 = '';
                if ($element->find('th', 0)) {
                    $prvek1 = $element->find('th', 0)->plaintext;
                    $prvek1 = str_replace(html_entity_decode('&nbsp;'), ' ', $prvek1);
                    $prvek1 = str_replace($hledej, $nahrad, $prvek1);
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

    function getAvailability($obsah_stranky, $produkt) {
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

    function getPopis($obsah_stranky, $produkt) {
        $produkt['popis'] = $obsah_stranky->find('div[id=popis-produktu]', 0)->plaintext;
        return $produkt;
    }

    function getParametr($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('table[class=sti_details]') as $tabulka) {
            $podminka = false;
            foreach ($tabulka->find('tr') as $parametry) {
                if ($podminka == true) {
                    $klic = '';
                    $hodnota = '';
                    if ($tabulka->find('td', 0)) {
                        $klic = $parametry->find('td', 0)->plaintext;
                    }
                    if ($tabulka->find('td', 1)) {
                        $hodnota = $parametry->find('td', 1)->plaintext;
                    }
                    if ($klic && $hodnota) {
                        $produkt['parametry'][$klic] = $hodnota;
                    }
                    if ($klic == 'Mám o tento produkt zájem:') {
                        unset($produkt[$klic]);
                    }
                }
                $podminka = true;
            }
        }
        return $produkt;
    }

    function getImage($obsah_stranky, $produkt) {
        $div = $obsah_stranky->find('div.sti_image', 0);
        if ($div) {
            $images = $div->find('img');
            if ($images) {
                foreach ($images as $image) {
                    $produkt["url_image"] = 'http://dealer.tsbohemia.cz/' . $image->src;
                }
            }
        }
        return $produkt;
    }

    function getInclusion($obsah_stranky, $produkt) {
        foreach ($obsah_stranky->find('div[id=zarazeni-produktu]') as $tabulka) {
            $podm = TRUE;
            $i = 0;
            $j = 0;
            $eee = $tabulka->find('strong.hcat', 0);
            while ($podm && $eee) {
                if ($eee->next_sibling() != null) {
                    if ($eee->tag == 'strong') {
                        $kategorie = $eee->plaintext;
                        $produkt['zarazeni'][$kategorie] = '';
                    }
                    if ($eee->tag == 'a') {
                        $produkt['zarazeni'][$kategorie] .= ' -> ' . $eee->plaintext;
                    }
                    $eee = $eee->next_sibling();
                } else {
                    $podm = FALSE;
                }
                $i++;
                $j++;
            }
        }
        return $produkt;
    }
}
