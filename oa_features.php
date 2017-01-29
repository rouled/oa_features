<?php
/**
 * 2017 Open agence
 *
 * @author    Open agence <contact@open-agence.com>
 * @copyright 2017 Open agence
 * @license   You only can use module, For any request send an e-mail!
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Oa_Features extends Module
{

    /**
     * OA_Features constructor.
     */
    public function __construct()
    {
        $this->name = 'oa_features';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Open agence';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Feature value image');
        $this->description = $this->l('Assign image to feature value');

    }

    /**
     * Install function
     *
     * @return bool
     */
    public function install()
    {
        if (false == parent::install()
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionAdminFeaturesControllerSaveAfter')
            || !$this->registerHook('actionFeatureValueDelete')
            || !$this->registerHook('displayProductListReviews')
            || !$this->registerHook('displayRightColumnProduct')
            || !$this->registerHook('displayFeatureValueForm')
            || !$this->registerHook('displayReassurance')
            || !$this->registerHook('displayProductPriceBlock')
            || !Configuration::updateValue('OA_FEATURE_PICTO_WIDTH', 32)
            || !Configuration::updateValue('OA_FEATURE_PICTO_HEIGHT', 32)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall function
     *
     * @return mixed
     */
    public function uninstall()
    {
        /* remove the configuration variable */
        Configuration::deleteByName('OA_FEATURE_PICTO_WIDTH');
        Configuration::deleteByName('OA_FEATURE_PICTO_HEIGHT');

        return parent::uninstall();
    }

    /**
     * Configuration content
     *
     * @return string
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitOA_FeaturesModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $this->renderForm() . $output;
    }

    /**
     * Save form data.
     *
     * @return null
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Set values for the inputs.
     *
     * @return array
     */
    protected function getConfigFormValues()
    {
        return array(
            'OA_FEATURE_PICTO_WIDTH' => Configuration::get('OA_FEATURE_PICTO_WIDTH'),
            'OA_FEATURE_PICTO_HEIGHT' => Configuration::get('OA_FEATURE_PICTO_HEIGHT')
        );
    }

    /**
     * Create the form that will be displayed in the configuration.
     *
     * @return mixed
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitOA_FeaturesModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     *
     * @return array
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Width'),
                        'name' => 'OA_FEATURE_PICTO_WIDTH',
                        'col' => 3,
                        'desc' => $this->l('Width of picto'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Height'),
                        'name' => 'OA_FEATURE_PICTO_HEIGHT',
                        'col' => 3,
                        'desc' => $this->l('Height of picto'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Hook display Header.
     *
     * @return null
     */
    public function hookDisplayHeader()
    {
        if (_PS_VERSION_ >= '1.7.0.0') {
            $this->context->controller->registerStylesheet('modules-oa_features',
                'modules/' . $this->name . '/views/css/oa_features.css', ['media' => 'all', 'priority' => 150]);
        } else {
            $this->context->controller->addCSS(($this->_path) . 'views/css/oa_features.css', 'all');
        }
    }

    /**
     * Hook display FeatureValueForm.
     *
     * @param $params
     *
     * @return mixed
     */
    public function hookDisplayFeatureValueForm($params)
    {
        $this->context->smarty->assign(
            array(
                'id_feature_value' => $params['id_feature_value'],
                'featurePicto' => $this->getFeaturePicto($params['id_feature_value']),
                'url_global' => _MODULE_DIR_ . $this->name . '/ajax.php',
            )
        );

        return $this->display(__FILE__, 'feature-form.tpl');
    }

    /**
     * Get feature picto
     *
     * @param int $id_feature
     *
     * @return bool|string
     */
    public function getFeaturePicto($id_feature)
    {
        if (empty($id_feature)) {
            return false;
        }

        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/img/' . (int)$id_feature . '.jpg')) {
            return _MODULE_DIR_ . $this->name . '/img/' . (int)$id_feature . '-default.jpg';
        } else {
            return false;
        }
    }

    /**
     * Hook action AdminFeaturesControllerSaveAfter
     *
     * @param $params
     *
     * @return null
     */
    public function hookActionAdminFeaturesControllerSaveAfter($params)
    {
        /* Resize, cut and optimize image */
        $maxUplodFile = 10;
        if ((!empty($params['return']->id)) && !empty($_FILES['filename']) && is_uploaded_file($_FILES['filename']['tmp_name'])) {
            $ext = pathinfo($_FILES['filename']['name'], PATHINFO_EXTENSION);
            if (!in_array(Tools::strtolower($ext), array('png', 'jpg', 'gif'))) {
                Tools::displayError('You can add image file only !');
            } elseif ($_FILES['filename']['size'] > ($maxUplodFile * 1024 * 1024)) {
                Tools::displayError(
                    sprintf(
                        $this->l('The file is too heavy.'),
                        ($maxUplodFile * 1024),
                        number_format(($_FILES['filename']['size'] / 1024), 2, '.', '')
                    )
                );
            } else {
                $res = ImageManager::resize($_FILES['filename']['tmp_name'],
                    _PS_MODULE_DIR_ . $this->name . '/img/' . (int)$params['return']->id . '.jpg');
                $res .= ImageManager::resize($_FILES['filename']['tmp_name'],
                    _PS_MODULE_DIR_ . $this->name . '/img/' . (int)$params['return']->id . '-default.jpg',
                    Configuration::get('OA_FEATURE_PICTO_WIDTH'), Configuration::get('OA_FEATURE_PICTO_HEIGHT'));

                if (!$res) {
                    $this->errors[] = Tools::displayError('Unable to resize one or more of your pictures.');
                }
            }
        }
    }

    /**
     * Hook action feature value delete
     *
     * @param $params
     */
    public function hookActionFeatureValueDelete($params)
    {
        $this->deletePicto($params['id_feature_value']);
    }

    /**
     * Delete picto
     *
     * @param $id_feature
     */
    public function deletePicto($id_feature)
    {
        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/img/' . $id_feature . '.jpg')) {
            unlink(_PS_MODULE_DIR_ . $this->name . '/img/' . $id_feature . '.jpg');
        }

        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/img/' . $id_feature . '-default.jpg')) {
            unlink(_PS_MODULE_DIR_ . $this->name . '/img/' . $id_feature . '-default.jpg');
        }
    }

    /**
     * Hook display ProductListReviews
     * @param $params
     *
     * @return mixed
     */
    public function hookDisplayProductListReviews($params)
    {
        $this->context->smarty->assign(
            array(
                'oaPictoWidth' => Configuration::get('OA_FEATURE_PICTO_WIDTH'),
                'oaPictoHeight' => Configuration::get('OA_FEATURE_PICTO_HEIGHT'),
                'oaFeatures' => $this->getProductFeatures((int)Context::getContext()->language->id,
                    (int)$params['product']['id_product'], true)
            )
        );
        return $this->display(__FILE__, 'product-listing.tpl');
    }

    /**
     * Get product features
     *
     * @param int $id_lang
     * @param int $id_product
     * @param bool $only_image
     *
     * @return array|bool
     */
    public function getProductFeatures($id_lang, $id_product, $only_image = false)
    {
        $features = $this->getFrontFeatures($id_lang, $id_product);

        if ($features) {
            $onlyImage = true;
            $featuresTab = array();
            foreach ($features as $feature) {
                $image = $this->getFeaturePicto($feature['id_feature_value']);
                if (($onlyImage || $only_image) && empty($image)) {
                    continue;
                }

                if (!empty($feature['value'])) {
                    $featuresTab[$feature['id_feature']]['name'] = $feature['name'];
                    $featuresTab[$feature['id_feature']]['values'][] = array(
                        'value' => $feature['value'],
                        'image' => $image
                    );
                }
            }
            return $featuresTab;
        }
        return false;
    }

    /**
     * Get front features
     *
     * @param int $id_lang
     * @param int $id_product
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getFrontFeatures($id_lang, $id_product)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            'SELECT fl.name, fvl.value, fp.id_feature, fvl.id_feature_value
            FROM ' . _DB_PREFIX_ . 'feature_product fp
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (fl.id_feature = fp.id_feature AND fl.id_lang = ' . (int)$id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON (fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = ' . (int)$id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON (f.id_feature = fp.id_feature AND fl.id_lang = ' . (int)$id_lang . ')
            ' . Shop::addSqlAssociation('feature', 'f') . '
            WHERE fp.id_product = ' . (int)$id_product
        );
    }

    /**
     * Hook display ProductListReviews
     *
     * @param $params
     *
     * @return mixed
     */
    public function hookDisplayProductPriceBlock($params)
    {
        if (_PS_VERSION_ >= '1.7.0.0') {
            if ($params['type'] == 'weight') {
                $this->context->smarty->assign(
                    array(
                        'oaPictoWidth' => Configuration::get('OA_FEATURE_PICTO_WIDTH'),
                        'oaPictoHeight' => Configuration::get('OA_FEATURE_PICTO_HEIGHT'),
                        'oaFeatures' => $this->getProductFeatures((int)Context::getContext()->language->id,
                            (int)$params['product']['id_product'], true)
                    )
                );
                return $this->fetch('module:oa_features/views/templates/hook/product-listing.tpl');
            }
        }
    }

    /**
     * Hook display RightColumnProduct
     *
     * @return bool
     */
    public function hookDisplayRightColumnProduct()
    {
        if (!($productId = Tools::getValue('id_product'))) {
            return false;
        }
        $this->context->smarty->assign(
            array(
                'oaPictoWidth' => Configuration::get('OA_FEATURE_PICTO_WIDTH'),
                'oaPictoHeight' => Configuration::get('OA_FEATURE_PICTO_HEIGHT'),
                'oaFeatures' => $this->getProductFeatures((int)Context::getContext()->language->id, (int)$productId,
                    true)
            )
        );
        return $this->display(__FILE__, 'product-extra.tpl');
    }

    /**
     * Hook display Reassurance.
     *
     * @return bool
     */
    public function hookDisplayReassurance()
    {
        if (_PS_VERSION_ >= '1.7.0.0') {
            if (!($productId = Tools::getValue('id_product'))) {
                return false;
            }

            $this->context->smarty->assign(
                array(
                    'oaPictoWidth' => Configuration::get('OA_FEATURE_PICTO_WIDTH'),
                    'oaPictoHeight' => Configuration::get('OA_FEATURE_PICTO_HEIGHT'),
                    'oaFeatures' => $this->getProductFeatures((int)Context::getContext()->language->id, (int)$productId,
                        true)
                )
            );
            return $this->fetch('module:oa_features/views/templates/hook/product-extra.tpl');
        }
    }

}

