<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Fruugo
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Helper\Product;


class HealthAndBeauty extends \Ced\Fruugo\Helper\Product\Base
{
    /**
     * Insert FoodAndBeverage Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @param string|[] $category
     * @param string|[] $type
     * @return string|[]
     */
    public function setData(
        $product,
        $attributes = [],
        $category = [],
        $type = [
        'type' => 'simple',
        'variantid' => null,
        'variantattr' => null,
        'isprimary' => '0'
        ]
    ) {
        $this->productObject = $product;
        $product = $product->toArray();

        $attributes['variantGroupId'] = 'blank';
        $attributes['variantAttributeNames/variantAttributeName'] = 'blank';
        $attributes['isPrimaryVariant'] = 'blank';
        $this->attributes = $attributes;
        $product = $this->extractSelectValues($product);
        $redundantAttributeCheck = [];

        if (isset($type['type'],$type['variantid'], $type['variantattr']) && !empty($type['variantid'])) {
            $attributes['variantGroupId'] = 'variantGroupId';
            $attributes['variantAttributeNames/variantAttributeName'] = 'variantAttributeNames/variantAttributeName';
            $attributes['isPrimaryVariant'] = 'isPrimaryVariant';

            $product['variantGroupId'] = $type['variantid'];
            $this->configurableAttributes =  $type['variantattr'];
            $product['variantAttributeNames/variantAttributeName'] = $type['variantattr'];
            $product['isPrimaryVariant'] = $type['isprimary'];
            $additionalAttributes =  $type['additionalAttributes'];
            $redundantAttributeCheck = array_flip($type['variantattr']);
            if(count($additionalAttributes['_value']) > 0) {
                foreach ($additionalAttributes['_value'] as $key => $value) {
                    # code...
                    if(isset($value['additionalProductAttribute']['productAttributeValue'])) {
                        $productValues[$value['additionalProductAttribute']['productAttributeName']] = $value['additionalProductAttribute']['productAttributeValue'];
                    }
                }
            }
            if($this->swatchEnabled) {
                if(isset($redundantAttributeCheck['color'])) {
                    $product['swatchImages/swatchImage/swatchImageUrl'] = $this->objectManager->get('\Magento\Catalog\Helper\Image')->init($this->productObject, 'swatch_image')->constrainOnly(TRUE)
                        ->keepAspectRatio(TRUE)
                        ->keepTransparency(TRUE)
                        ->keepFrame(FALSE)->resize(100,100)->getUrl();
                    $product['swatchImages/swatchImage/swatchVariantAttribute'] = 'color';
                    $attributes['swatchImages/swatchImage/swatchImageUrl'] = 'swatchImages/swatchImage/swatchImageUrl';
                    $attributes['swatchImages/swatchImage/swatchVariantAttribute'] = 'swatchImages/swatchImage/swatchVariantAttribute';
                } elseif(isset($redundantAttributeCheck['pattern'])) {
                    $product['swatchImages/swatchImage/swatchImageUrl'] = $this->objectManager->get('\Magento\Catalog\Helper\Image')->init($this->productObject, 'swatch_image')->constrainOnly(TRUE)
                        ->keepAspectRatio(TRUE)
                        ->keepTransparency(TRUE)
                        ->keepFrame(FALSE)->resize(100,100)->getUrl();
                    $product['swatchImages/swatchImage/swatchVariantAttribute'] = 'pattern';
                    $attributes['swatchImages/swatchImage/swatchImageUrl'] = 'swatchImages/swatchImage/swatchImageUrl';
                    $attributes['swatchImages/swatchImage/swatchVariantAttribute'] = 'swatchImages/swatchImage/swatchVariantAttribute';
                } else {
                    foreach ($redundantAttributeCheck as $key => $attribute) {
                        if(!isset($confAttributeFlag[$key])) {
                            $product['swatchImages/swatchImage/swatchImageUrl'] = $this->objectManager->get('\Magento\Catalog\Helper\Image')->init($this->productObject, 'swatch_image')->constrainOnly(TRUE)
                                ->keepAspectRatio(TRUE)
                                ->keepTransparency(TRUE)
                                ->keepFrame(FALSE)->resize(100,100)->getUrl();
                            $product['swatchImages/swatchImage/swatchVariantAttribute'] = $key;
                            $attributes['swatchImages/swatchImage/swatchImageUrl'] = 'swatchImages/swatchImage/swatchImageUrl';
                            $attributes['swatchImages/swatchImage/swatchVariantAttribute'] = 'swatchImages/swatchImage/swatchVariantAttribute';
                            break;
                        }
                    }
                }
            }
        }

        $data = [];
        if (!empty($product) && !empty($attributes) && !empty($category)) {
            $fruugoAttr = [
                'swatchImages/swatchImage/swatchImageUrl', 'swatchImages/swatchImage/swatchVariantAttribute',
                'collection', 'variantAttributeNames/variantAttributeName', 'flexibleSpendingAccountEligible',
                'variantGroupId', 'isPrimaryVariant', 'fabricContent/fabricContentValue', 'isAdultProduct',
                'fabricCareInstructions/fabricCareInstruction', 'brand', 'manufacturer', 'modelNumber',
                'manufacturerPartNumber', 'gender', 'color/colorValue', 'ageGroup/ageGroupValue', 'isReusable',
                'isDisposable', 'material/materialValue', 'isPowered', 'numberOfPieces', 'character/characterValue',
                'powerType', 'isPersonalizable', 'bodyParts/bodyPart', 'isPortable', 'cleaningCareAndMaintenance',
                'isSet', 'isTravelSize', 'recommendedUses/recommendedUse', 'shape',
                'compatibleBrands/compatibleBrand'
            ];
            foreach ($fruugoAttr as $attr) {
                if(!isset($redundantAttributeCheck[explode('/', $attr)[0]])) {
                    if (isset($product[$attributes[$attr]]) && !empty($product[$attributes[$attr]]) ) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                    }
                } else {
                    if (isset($product[$attributes[$attr]]) && !empty($product[$attributes[$attr]]) ) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $productValues[explode('/', $attr)[0]]));
                    }
                }
            }
            switch ($category['cat_id']) {
                case 'HealthAndBeautyElectronics' : {
                    $data['HealthAndBeautyElectronics'] =
                        $this->setHealthAndBeautyElectronics($product, $attributes);
                    break;
                    }
                case 'Optical' : {
                    $data['Optical'] =
                        $this->setOptical($product, $attributes);
                    break;
                    }
                case 'MedicalAids' : {
                    $data['MedicalAids'] =
                        $this->setMedicalAids($product, $attributes);
                    break;
                    }
                case 'PersonalCare' : {
                    $data['PersonalCare'] =
                        $this->setPersonalCare($product, $attributes);
                    break;
                    }
                case 'MedicineAndSupplements' : {
                    $data['MedicineAndSupplements'] =
                        $this->setMedicineAndSupplements($product, $attributes);
                    break;
                    }
            }
        }
        return $data;
    }

    /**
     * Insert HealthAndBeautyElectronics Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setHealthAndBeautyElectronics($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'batteriesRequired', 'batterySize', 'connections/connection',
            'isCordless','hasAutomaticShutoff', 'screenSize/unit', 'screenSize/measure','displayTechnology'
        ];
        $data = [];

        if (!empty($product) && !empty($attributes)) {
            foreach ($fruugoAttr as $attr) {
                 try{
                    if (!empty($product[$attributes[$attr]])) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                    }
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        return $data;
    }

    /**
     * Insert Optical Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setOptical($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'frameMaterial/frameMaterialValue', 'shape', 'eyewearFrameStyle',
            'lensMaterial','eyewearFrameSize','uvRating', 'isPolarized','lensTint','isScratchResistant',
            'hasAdaptiveLenses', 'lensType/lensTypeValue'
        ];
        $data = [];

        if (!empty($product) && !empty($attributes)) {
            foreach ($fruugoAttr as $attr) {
                try{
                    if (!empty($product[$attributes[$attr]])) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                    }
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        return $data;
    }

    /**
     * Insert MedicalAids Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setMedicalAids($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'isInflatable', 'isWheeled', 'isFoldable', 'isIndustrial',
            'diameter/unit', 'diameter/measure', 'isAssemblyRequired','assemblyInstructions','maximumWeight/unit',
            'maximumWeight/unit','isLatexFree','isAntiAging', 'isHypoallergenic','isOilFree','isParabenFree',
            'isNoncomodegenic','scent', 'isUnscented','isVegan', 'isWaterproof','isWaterproof',
            'healthConcerns/healthConcern'
        ];
        $data = [];

        if (!empty($product) && !empty($attributes)) {
            foreach ($fruugoAttr as $attr) {
                try{
                    if (!empty($product[$attributes[$attr]])) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                    }
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        return $data;
    }

    /**
     * Insert PersonalCare Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setPersonalCare($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'ingredientClaim/ingredientClaimValue', 'isLatexFree',
            'absorbency', 'resultTime/unit', 'resultTime/measure', 'skinCareConcern','skinType','hairType',
            'skinTone','spfValue','isAntiAging', 'isHypoallergenic','isOilFree','isParabenFree',
            'isNoncomodegenic','scent','isUnscented','isVegan', 'isWaterproof','isTinted','isSelfTanning',
            'isDrugFactsLabelRequired','drugFactsLabel', 'activeIngredients/activeIngredient/activeIngredientName',
            'activeIngredients/activeIngredient/activeIngredientPercentage', 'inactiveIngredients/inactiveIngredient',
            'form','organicCertifications/organicCertification','instructions',
            'stopUseIndications/stopUseIndication'
        ];
        $data = [];

        if (!empty($product) && !empty($attributes)) {
            foreach ($fruugoAttr as $attr) {
                try{
                    if (!empty($product[$attributes[$attr]])) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                    }
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        return $data;
    }

    /**
     * Insert MedicineAndSupplements Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setMedicineAndSupplements($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'isDrugFactsLabelRequired', 'drugFactsLabel',
            'isSupplementFactsLabelRequired', 'supplementFactsLabel', 'servingSize', 'servingsPerContainer',
            'activeIngredients/activeIngredient/activeIngredientName',
            'activeIngredients/activeIngredient/activeIngredientPercentage',
            'inactiveIngredients/inactiveIngredient', 'healthConcerns/healthConcern','form',
            'organicCertifications/organicCertification','instructions','dosage',
            'stopUseIndications/stopUseIndication'
        ];
        $data = [];

        if (!empty($product) && !empty($attributes)) {
            foreach ($fruugoAttr as $attr) {
                try{
                    if (!empty($product[$attributes[$attr]])) {
                        $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                    }
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        return $data;
    }

}