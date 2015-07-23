class ProductInfoEil {
    
    const PRODUCT_URL = 'http://eil.com/'; 
    
    static function getProduct($url, $soubor) {
        $obsah_stranky = file_get_html($url);
        if ($obsah_stranky == false) {
            printr('Nelze načíst stránku');
        }/* elseif (!self::isProduct($obsah_stranky)) {
            printr('Produkt nenalezen ' . $url);
            $produkt = array('status' => 'Produkt nenalezen ', 'url' => $url);
            self::saveProduct($produkt, $soubor);
        } */else {
            $produkt = array();
            $produkt = self::getName($obsah_stranky, $produkt);
         /*   $produkt = self::getInformation($obsah_stranky, $produkt);
            $produkt = self::getAvailability($obsah_stranky, $produkt);
            $produkt = self::getPopis($obsah_stranky, $produkt);
            $produkt = self::getParametr($obsah_stranky, $produkt);
            $produkt = self::getImage($obsah_stranky, $produkt);
            $produkt = self::getInclusion($obsah_stranky, $produkt);
            $produkt = self::unsetInformation($produkt);*/
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
 
    private static function eraseSpace($promenna) {
        $promenna = str_replace(html_entity_decode('&nbsp;'), ' ', $promenna);
        $promenna = trim($promenna, ':');
        return trim($promenna);
    }

    private static function getName($obsah_stranky, $produkt) {
        $name = self::findFirst($obsah_stranky, 'title')->plaintext;
        $produkt['nazev'] = self::eraseSpace($name);
        return $produkt;
    }
    
    
}
