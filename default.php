<?php

require 'ProductInfoTSBohemia.php';
require 'ProductInfoEil.php';
require 'ProductInfoIce.php';

set_time_limit(300);

echo 'Hello World';

printr('***POKUS***');

$novinky = FNovinky::getall(true, LANG);
foreach ($novinky as $p) {
    printr($p['name']);
}

$pages = FStranka::nactigalerii(9);
foreach ($pages as $img) {
    ?>
    <img src="<?= FStranka::imgLinkGallery($img, 'middle') ?>"> <?php
    printr($img['description']);
}

/* $pages = FStranka::get(11);
  printr($pages);
 */
/* $html = file_get_html('http://www.idnes.cz/');

  foreach($html->find('h2') as $element) {
  printr ($element . '<br>');
  }
 */

/* foreach ($produkt as $parametr) {
  printr($parametr);
  } */

/*$url_produkty = array();

for ($cislo = 0; $cislo < 30; $cislo++) {
    $url_produkty[] = 212486 + $cislo;
}

$soubor = 'produkty.txt';

if (file_exists($soubor)) {
    unlink($soubor);
}

foreach ($url_produkty as $url_produkty) {
    ProductInfoTSBohemia::getProduct(ProductInfoTSBohemia::SERVER_URL . '?cls=stoitem&stiid=' . $url_produkty, $soubor);
}*/

$soubor = 'produkt.txt';

if (file_exists($soubor)) {
    unlink($soubor);
}

ProductInfoEil::getProduct(ProductInfoEil::SERVER_URL . 'shop/moreinfo.asp?catalogid=587277', $soubor);

//getProduct('http://dealer.tsbohemia.cz/?cls=stoitem&stiid=212486');

/* $link = @mysql_connect('localhost', 'root', '')
  or die('Could not connect: ' . mysql_error());
  echo 'Connected successfully';
  mysql_select_db('nfwtest') or die('Could not select database');

  $query = 'SELECT * FROM history';
  $result = mysql_query($query) or die('Query failed: ' . mysql_error());
  function getProduct($url) {
  $obsah_stranky = file_get_html($url);
  foreach ($obsah_stranky->find('table[class=sti_detail sti_detail_head]') as $tabulka) {
  foreach ($tabulka->find('tr') as $element) {
  $prvek1 = $element->find('th', 0)->plaintext;
  $prvek2 = $element->find('td', 0)->plaintext;
  if ($prvek1) {
  $produkt[$prvek1] = $prvek2;
  }
  }
  foreach ($obsah_stranky->find('table[class=sti_detail_avail]') as $tabulka) {
  $i = 0;
  foreach ($tabulka->find('th') as $dostup) {
  echo $dostup->plaintext;
  echo $tabulka->find('img', $i++)->class;
  }
  }
  }

  printr($produkt);
  }
  echo "<table>\n";
  while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
  echo "\t<tr>\n";
  foreach ($line as $col_value) {
  echo "\t\t<td>$col_value</td>\n";
  }
  echo "\t</tr>\n";
  }
  echo "</table>\n";

  mysql_free_result($result);

  mysql_close($link); */

/*  menu_vypisuroven(1, 1);

  $pages = FStruktura::vratdeti(1);
  strom($pages, 1, 0);

  function strom($pages, $id, $i) {
  $i += 1;
  foreach ($pages as $p) {
  printr($i . " " . $p['name']);
  $id = $p['id'];
  $pages = FStruktura::vratdeti($id);
  strom($pages, $id, $i);
  }
  }
 */
/* $pages = FStruktura::vratdeti(6);

  foreach ($pages as $p) {
  printr($p['name']);
  }

  printr(FStranka::getText(27));
 */
if (USER_IS_LOGGED) {
    echo '<p><b>Obsah viditelný pouze pro přihlášeného uživatele.</b></p><br/>';
}
