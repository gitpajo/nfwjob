class ProductInfoEil {
    
    const PRODUCT_URL = 'http://eil.com/shop/moreinfo.asp?catalogid=587277'; 
 
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
