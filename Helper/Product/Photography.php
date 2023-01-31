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


class Photography extends \Ced\Fruugo\Helper\Product\Base
{
    /**
     * Insert Photography Category Data
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
                'swatchImages/swatchImage/swatchVariantAttribute','accessoriesIncluded/accessoriesIncludedValue',
                'variantAttributeNames/variantAttributeName','variantGroupId','isPrimaryVariant','isWeatherResistant',
                'hasSignalBooster','hasWirelessMicrophone','brand','manufacturer','modelNumber',
                'manufacturerPartNumber','gender','color/colorValue','batteriesRequired','batterySize',
                'memoryCardType/memoryCardTypeValue','connections/connection','material/materialValue','numberOfPieces',
                'isPortable','cleaningCareAndMaintenance','recommendedLocations/recommendedLocation',
                'isAssemblyRequired','assemblyInstructions','isWaterproof',
                'hasTouchscreen','recordableMediaFormats/recordableMediaFormat','compatibleBrands/compatibleBrand',
                'compatibleDevices/compatibleDevice','wirelessTechnologies/wirelessTechnology'
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
                case 'PhotoAccessories' : {
                    $data['PhotoAccessories'] = $this->setPhotoAccessories($product, $attributes);
                    break;
                }
                case 'CamerasAndLenses' : {
                    $data['CamerasAndLenses'] = $this->setCamerasAndLenses($product, $attributes);
                    break;
                }
            }
        }
        return $data;
    }


    /**
     * Insert Photography/PhotoAccessories Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setPhotoAccessories($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'fabricContent/fabricContentValue', 'condition', 'pattern/patternValue', 'isRemoteControlIncluded',
            'isMadeFromRecycledMaterial', 'occasion/occasionValue', 'hardOrSoftCase','isCordless','lightOutput/unit',
            'lightOutput/measure','maximumWeight/unit','maximumWeight/measure','capacity', 'volts/unit','volts/measure',
            'watts/unit','watts/measure','shape', 'inputsAndOutputs/inputsAndOutput/inputOutputType',
            'inputsAndOutputs/inputsAndOutput/inputOutputQuantity','displayTechnology','hasBluetooth','lightBulbType',
            'wirelessTechnologies/wirelessTechnologie'
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
     * Insert Photography/CamerasAndLenses Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setCamerasAndLenses($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'ageGroup/ageGroupValue', 'powerType', 'diameter/unit', 'diameter/measure','numberOfMegapixels/unit',
            'numberOfMegapixels/measure','focalLength/measure','focalLength/unit', 'hasShoulderStrap','hasHandle',
            'magnification','fieldOfView','isFogResistant','lensDiameter/unit','lensDiameter/measure','isMulticoated',
            'shootingPrograms','shootingMode','opticalZoom','selfTimerDelay/unit','selfTimerDelay/measure',
            'hasSelfTimer','hasRemovableFlash','digitalZoom','focusType/focusTypeValue','hasRedEyeReduction',
            'minimumShutterSpeed/unit','minimumShutterSpeed/unit','lockType','maximumShutterSpeed/unit',
            'maximumShutterSpeed/measure','sensorResolution/unit','sensorResolution/measure','maximumShootingSpeed',
            'minimumAperture','hasDovetailBarSystem','hasLcdScreen','maximumAperture','hasMemoryCardSlot',
            'microphoneIncluded','hasNightVision','lensFilterType','isParfocal','flashType','filmCameraType',
            'attachmentStyle','exposureModes/exposureMode','cameraLensType','displayResolution/unit',
            'displayResolution/measure','focalRatio','lensCoating','operatingTemperature/unit',
            'operatingTemperature/measure','isLockable','lensType/lensTypeValue','screenSize/unit','screenSize/measure',
            'displayTechnology','hasFlash','standbyTime/unit','standbyTime/measure',
            'activeIngredients/activeIngredient/activeIngredientPercentage', 'inactiveIngredients/inactiveIngredient',
            'form','instructions'
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