<?php
/**
 * 2017 Open agence
 *
 * @author    Open agence <contact@open-agence.com>
 * @copyright 2017 Open agence
 * @license   You only can use module, For any request send an e-mail!
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

$oa_features = Module::getInstanceByName('oa_features');
if (Tools::getValue('id_feature')) {
    $oa_features->deletePicto((int)Tools::getValue('id_feature'));
}
