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


class MusicalInstrument extends \Ced\Fruugo\Helper\Product\Base
{
    /**
     * Insert MusicalInstrument Category Data
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
                'swatchImages/swatchImage/swatchVariantAttribute','variantGroupId',
                'variantAttributeNames/variantAttributeName','isPrimaryVariant','brand','condition','manufacturer',
                'modelNumber','manufacturerPartNumber','color/colorValue','material/materialValue',
                'numberOfPieces','isPersonalizable','isPortable','recommendedUses/recommendedUse',
                'recommendedLocations/recommendedLocation'
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
                case 'SoundAndRecording' : {
                    $data['SoundAndRecording'] = $this->setSoundAndRecording($product, $attributes);
                    break;
                    }
                case 'InstrumentAccessories' : {
                    $data['InstrumentAccessories'] = $this->setInstrumentAccessories($product, $attributes);
                    break;
                    }
                case 'MusicalInstruments' : {
                    $data['MusicalInstruments'] = $this->setMusicalInstruments($product, $attributes);
                    break;
                    }
                case 'MusicCasesAndBags' : {
                    $data['MusicCasesAndBags'] = $this->setMusicCasesAndBags($product, $attributes );
                    break;
                    }
            }
        }
        return $data;
    }

    /**
     * Insert SoundAndRecording Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setSoundAndRecording($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'hasSignalBooster','hasWirelessMicrophone','batteriesRequired','batterySize','isPowered','powerType',
            'isRemoteControlIncluded','audioPowerOutput','equalizerControl',
            'inputsAndOutputs/inputsAndOutput/inputOutputType',
            'inputsAndOutputs/inputsAndOutput/inputOutputQuantity','hasIntegratedSpeakers','hasBluetooth',
            'batteryLife/unit','batteryLife/measure','wirelessTechnologies/wirelessTechnology'
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
     * Insert InstrumentAccessories Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setInstrumentAccessories($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'hasSignalBooster','hasWirelessMicrophone','batteriesRequired',
            'pattern/patternValue',
            'batterySize','isRemoteControlIncluded','instrument/instrumentValue',
            'inputsAndOutputs/inputsAndOutput/inputOutputType',
            'inputsAndOutputs/inputsAndOutput/inputOutputQuantity',
            'displayTechnology','hasBluetooth','batteryLife/unit','batteryLife/measure',
            'wirelessTechnologies/wirelessTechnology'
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
     * Insert MusicalInstruments Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setMusicalInstruments($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'finish','hasSignalBooster','hasWirelessMicrophone','ageGroup/ageGroupValue','batteriesRequired',
            'batterySize','powerType','isPortable','recommendedUses/recommendedUse',
            'recommendedLocations/recommendedLocation',
            'audioPowerOutput','isCollectible','musicalInstrumentFamily','isAcoustic','isElectric','isFretted',
            'instrument/instrumentValue','shape','inputsAndOutputs/inputsAndOutput/inputOutputType',
            'inputsAndOutputs/inputsAndOutput/inputOutputQuantity',
            'hasIntegratedSpeakers','displayTechnology','hasBluetooth','batteryLife/unit','batteryLife/measure'
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
     * Insert MusicCasesAndBags Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setMusicCasesAndBags($product = [], $attributes = [])
    {
        $fruugoAttr = [

            'fabricContent/fabricContentValue','fabricCareInstructions/fabricCareInstruction','hardOrSoftCase',
            'isWheeled','instrument/instrumentValue','shape'
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