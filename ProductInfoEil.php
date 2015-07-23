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
            /*  $produkt = self::getAvailability($obsah_stranky, $produkt);
              $produkt = self::getPopis($obsah_stranky, $produkt);
              $produkt = self::getParametr($obsah_stranky, $produkt);
              $produkt = self::getImage($obsah_stranky, $produkt);
              $produkt = self::getInclusion($obsah_stranky, $produkt);
              $produkt = self::unsetInformation($produkt); */
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

// Funkce pro zjištění, zda produkt se zadaným url existuje
    /* private static function isProduct($obsah_stranky) {
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
      } */

// Funkce pro přejmenování informací o produktu
 /*   private static function renameInformation($prvek1) {
        $seznam = array(
            'Tracklisting' => 'stopy',
            'Condition' => 'stav',
            'Availability' => 'dostupnost',
            'Year of Release' => 'rok',
            'Artist' => 'umelec',
            'Title' => 'dilo',
            'Price' => 'cena',
            'Format' => 'format',
            'Record Label' => 'vydavatel',
            'Country of Origin' => 'zeme_puvodu',
            'EIL.COM Ref No' => 'kod',
            'Related Artists' => 'souvisejici_umelci'
        );
        $hledej = array_keys($seznam);
        $nahrad = array_values($seznam);
        $prvek1 = str_replace(html_entity_decode('&nbsp;'), ' ', $prvek1);
        $prvek1 = str_replace($hledej, $nahrad, $prvek1);
        return $prvek1;
    }*/

// Funkce pro smazání informací o produktu
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

// Funkce pro smazání přebytečných mezer
    private static function eraseSpace($promenna) {
        $promenna = str_replace(html_entity_decode('&nbsp;'), ' ', $promenna);
        $promenna = trim($promenna, ':');
        return trim($promenna);
    }

// Funkce pro vypsání názvu produktu
    private static function getName($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getTracks($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['stopy'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getCondition($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['stav'] = self::eraseSpace($name);
        return $produkt;
    }
    private static function getAvailability($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['dostupnost'] = self::eraseSpace($name);
        return $produkt;
    }
    

    private static function getYear($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['rok'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getArtist($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['umelec'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getTitle($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['dilo'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getPrice($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['cena'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getFormat($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['format'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getRecordLabel($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['vydavatel'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getCountry($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['zeme_puvodu'] = self::eraseSpace($name);
        return $produkt;
    }

    private static function getCode($obsah_stranky, $produkt) {
        $name = $obsah_stranky->find('span[itemprop=name]')->plaintext;
        $produkt['kod'] = self::eraseSpace($name);
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
                        $produkt['stopy'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Condition:') {
                        $produkt['stav'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Availability:') {
                        $produkt['dostupnost'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Year of Release:') {
                        $produkt['rok'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Artist:') {
                        $produkt['umelec'] = self::eraseSpace($bunka->next_sibling()->plaintext);
                    } else if ($bunka->plaintext == 'Title:') {
                        $produkt['dilo'] = self::eraseSpace($bunka->next_sibling()->plaintext);
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
                    }
                }
            }
            $podminka = true;
        }
        return $produkt;
    }

    /*   private static function getInformation($obsah_stranky, $produkt) {
      $table = $obsah_stranky->find('TABLE BORDER="0"', 1);
      $radky = $table->find('tr');
      foreach ($radky as $radek) {
      $klic = self::findFirst($radek, 'td')->plaintext;
      $hodnota = $radek->find('td', 1)->plaintext;
      $produkt['informace'][$klic] = $hodnota;
      if ($radek->next_sibling() == null) {
      break;
      }
      }
      return $produkt;
      } */

// Funkce pro vypsání dostupnosti produktu na jednotlivých prodejnách
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

// Funkce pro vypsání popisu produktu
    private static function getPopis($obsah_stranky, $produkt) {
        $popis = self::findFirst($obsah_stranky, 'div[id=popis-produktu]')->plaintext;
        $produkt['popis'] = self::eraseSpace($popis);
        return $produkt;
    }

// Funkce pro vypsání parametrů produktu
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

// Funkce pro vypsání url obrázků produktu
    private static function getImage($obsah_stranky, $produkt) {

        $div = self::findFirst($obsah_stranky, 'div.sti_image');
        $i = 1;
        if ($div) {
            $images = $div->find('img');
            if ($images) {
                foreach ($images as $image) {
                    $produkt['gallery_url_image']['Image ' . $i] = self::SERVER_URL .
                            $image->src;
                    $i++;
                }
            }
        }
        $divs = $obsah_stranky->find('div[class=sti_detail_gallery]');
        foreach ($divs as $div) {
            $produkt['gallery_url_image']['Image ' . $i] = self::SERVER_URL .
                    self::findFirst($div, 'img')->src;
            $i++;
        }
        return $produkt;
    }

// Funkce pro vypsání zařazení produktu do dané kategorie
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
