<?php
/**
    Käesoleva loomingu autoriõigused kuuluvad Matis Halmannile ja Aktsiamaailm OÜ-le
    Litsentsitingimused on saadaval http://www.e-abi.ee/litsents
    @version 1.0
    @date 2012-09-01
*/

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/banklinkmaksekeskus.php');

//if (!$cookie->isLogged())
//    Tools::redirect('authentication.php?back=order.php');
$bankwire = new Banklinkmaksekeskus();
echo $bankwire->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

