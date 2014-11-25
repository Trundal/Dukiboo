<?php

/* 
 *   
 *  Copyright 2013 Aktsiamaailm OÜ
 *  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents.
 *  

 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * 
 * @param BanklinkMaksekeskus $module
 * @return boolean
 */
function upgrade_module_0_2($module) {
    // Process Module upgrade to 0.4
    // ....
    return $module->upgrade_module_0_2();
}
