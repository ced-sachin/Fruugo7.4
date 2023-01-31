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


class SportAndRecreation extends \Ced\Fruugo\Helper\Product\Base
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

        $product['blank'] = '';
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
                'swatchImages/swatchImage/swatchImageUrl',
                'swatchImages/swatchImage/swatchVariantAttribute',
                'variantAttributeNames/variantAttributeName', 'variantGroupId','isPrimaryVariant',
                'fabricContent/fabricContentValue/materialName','isWeatherResistant','finish',
                'fabricCareInstructions/fabricCareInstruction',
                'brand', 'condition','manufacturer', 'modelNumber','manufacturerPartNumber','clothingSize',
                'gender', 'color/colorValue',
                'ageGroup/ageGroupValue', 'batteriesRequired', 'dexterity', 'batterySize',
                'fishingLinePoundTest', 'fishingLocation', 'animalType', 'material/materialValue',
                'pattern/patternValue','isPowered','numberOfPieces','character/characterValue','fitnessGoal',
                'powerType','maximumIncline/unit','maximumIncline/measure','isPortable',
                'cleaningCareAndMaintenance','bladeType','recommendedUses/recommendedUse','tentType',
                'recommendedLocations/recommendedLocation','seatingCapacity','tireDiameter/unit',
                'tireDiameter/measure','season/seasonValue','isWheeled','isMemorabilia','isFoldable',
                'isCollectible','isAssemblyRequired','maximumRecommendedAge/unit',
                'maximumRecommendedAge/measure','assemblyInstructions','minimumRecommendedAge/unit',
                'minimumRecommendedAge/measure','ballCoreMaterial/ballCoreMaterialValue',
                'footballSize','sport','basketballSize','maximumWeight/unit','maximumWeight/measure',
                'soccerBallSize','batDrop','isTearResistant','isSpaceSaving','capacity','velocity/unit',
                'velocity/measure','isWaterproof','hasAutomaticShutoff','shape',
                'wirelessTechnologies/wirelessTechnologie','horsepower/unit','horsepower/measure'
            ];
            foreach ($fruugoAttr as $attr) {
                try{
                    if(!isset($redundantAttributeCheck[explode('/', $attr)[0]])) {
                        if (isset($product[$attributes[$attr]]) && !empty($product[$attributes[$attr]]) ) {
                            $data = array_merge_recursive($data, $this->generateArray($attr, $product[$attributes[$attr]]));
                        }
                    } else {
                        if (isset($product[$attributes[$attr]]) && !empty($product[$attributes[$attr]]) ) {
                            $data = array_merge_recursive($data, $this->generateArray($attr, $productValues[explode('/', $attr)[0]]));
                        }
                    }
                } catch(\Exception $e) {
                    continue;
                }
            }
            switch ($category['cat_id']) {
                case 'Cycling' : {
                    $data['Cycling'] = $this->setCycling($product, $attributes);
                    break;
                    }
                case 'Optics' : {
                        $data['Optics'] = $this->setCycling($product, $attributes);
                        break;
                        }
                case 'Weapons' : {
                        $data['Weapons'] = $this->setCycling($product, $attributes);
                        break;
                        }
                }
        }
        return $data;
    }

    /**
     * Insert Cycling Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setCycling($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'bicycleFrameSize/unit','bicycleFrameSize/measure','bicycleWheelDiameter/unit',
            'bicycleWheelDiameter/measure','bicycleTireSize',
            'numberOfSpeeds','lightBulbType'
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
     * Insert Optics Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setOptics($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'powerType','magnification','fieldOfView','isFogResistant','lensDiameter/unit','lensDiameter/measure',
            'isMulticoated','opticalZoom','digitalZoom','focusType/focusTypeValue',
            'lockType','sensorResolution/unit','sensorResolution/measure','hasDovetailBarSystem','hasLcdScreen',
            'hasMemoryCardSlot','hasNightVision','isParfocal',
            'attachmentStyle','displayResolution/unit','displayResolution/measure','focalRatio','lensCoating',
            'operatingTemperature/unit','operatingTemperature/measure','isLockable','screenSize/unit',
            'screenSize\measure','displayTechnology'
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
     * Insert Weapons Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setWeapons($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'shotgunGauge','velocity/unit','velocity/measure','firearmAction','caliber/unit',
            'caliber/measure','ammunitionType','firearmChamberLength',
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