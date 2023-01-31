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


class Electronics extends \Ced\Fruugo\Helper\Product\Base
{
    /**
     * Insert Electronics Category Data
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
            $fruugoAttr =  [
                'swatchImages/swatchImage/swatchImageUrl', 'swatchImages/swatchImage/swatchVariantAttribute',
                'variantAttributeNames/variantAttributeName', 'variantGroupId', 'isPrimaryVariant',
                'isEnergyGuideLabelRequired', 'energyGuideLabel', 'hasSignalBooster', 'hasWirelessMicrophone',
                'brand', 'manufacturer', 'modelNumber', 'manufacturerPartNumber', 'color/colorValue', 'ageGroup',
                'batteriesRequired', 'batterySize', 'isEnergyStarCertified', 'connections/connection',
                'material/materialValue', 'numberOfPieces', 'isRemoteControlIncluded', 'isPersonalizable',
                'isPortable', 'isCordless', 'recommendedUses/recommendedUse',
                'recommendedLocations/recommendedLocation', 'audioPowerOutput', 'peakAudioPowerCapacity/unit',
                'peakAudioPowerCapacity/measure', 'audioFeatures/audioFeature', 'numberOfChannels', 'resolution',
                'platform'
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
                case 'VideoProjectors' : {
                    $data['VideoProjectors'] = $this->setVideoProjectors($product, $attributes);
                    break;
                    }
                case 'Computers' : {
                    $data['Computers'] = $this->setComputers($product, $attributes);
                    break;
                    }
                case 'ElectronicsAccessories' : {
                    $data['ElectronicsAccessories'] = $this->setElectronicsAccessories($product, $attributes);
                    break;
                    }
                case 'ComputerComponents' : {
                    $data['ComputerComponents'] = $this->setComputerComponents($product, $attributes);
                    break;
                    }
                case 'Software' : {
                    $data['Software'] = $this->setSoftware($product, $attributes);
                    break;
                    }
                case 'VideoGames' : {
                    $data['VideoGames'] = $this->setVideoGames($product, $attributes);
                    break;
                    }
                case 'PrintersScannersAndImaging' : {
                    $data['PrintersScannersAndImaging'] =
                        $this->setPrintersScannersAndImaging($product, $attributes);
                    break;
                    }
                case 'ElectronicsCables' : {
                    $data['ElectronicsCables'] =
                        $this->setElectronicsCables($product, $attributes);
                    break;
                    }
                case 'TVsAndVideoDisplays' : {
                    $data['TVsAndVideoDisplays'] =
                        $this->setTVsAndVideoDisplays($product, $attributes);
                    break;
                    }
                case 'CellPhones' : {
                    $data['CellPhones'] =
                        $this->setCellPhones($product, $attributes);
                    break;
                    }

            }
        }
        return $data;
    }

    /**
     * Insert VideoProjectors Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setVideoProjectors($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'aspectRatio', 'brightness/unit', 'brightness/measure', 'nativeResolution', 'maximumContrastRatio',
            'throwRatio', 'lampLife/unit', 'lampLife/measure', 'has3dCapabilities',
            'inputsAndOutputs/inputsAndOutput', 'hasIntegratedSpeakers', 'screenSize/unit',
            'screenSize/measure', 'displayTechnology', 'wirelessTechnologies/wirelessTechnology'
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
     * Insert CellPhones Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setCellPhones($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'cellPhoneType', 'resolution', 'screenSize/unit', 'screenSize/measure',
            'mobileOperatingSystem/mobileOperatingSystemValue', 'modelName', 'displayTechnology', 'hasBluetooth',
            'batteryLife/unit', 'batteryLife/measure', 'cellPhoneServiceProvider', 'cellularNetworkTechnology',
            'frontFacingCameraMegapixels/unit', 'frontFacingCameraMegapixels/measure', 'hasFlash',
            'standbyTime/unit', 'standbyTime/measure', 'talkTime/unit', 'talkTime/measure',
            'rearCameraMegapixels/unit', 'rearCameraMegapixels/measure', 'maximumRamSupported/unit',
            'maximumRamSupported/measure', 'processorSpeed/unit', 'processorSpeed/measure',
            'processorType/processorTypeValue', 'ramMemory/unit', 'ramMemory/measure',
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
     * Insert TVsAndVideoDisplays Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setTVsAndVideoDisplays($product = [], $attributes = [])
    {
         $fruugoAttr = [
             'televisionType/televisionTypeValue', 'hasTouchscreen', 'backlightType', 'refreshRate/unit',
             'refreshRate/measure', 'responseTime/unit', 'responseTime/measure', 'aspectRatio',
             'nativeResolution', 'maximumContrastRatio', 'inputsAndOutputs/inputsAndOutput',
             'hasIntegratedSpeakers', 'resolution', 'screenSize/unit', 'screenSize/measure', 'displayTechnology',
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
     * Insert ElectronicsCables Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setElectronicsCables($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'connectorFinish', 'cableLength/unit', 'cableLength/measure', 'numberOfTwistedPairsPerCable',
            'compatibleDevices/compatibleDevice'
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
     * Insert PrintersScannersAndImaging Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setPrintersScannersAndImaging($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'hasAutomaticDocumentFeeder', 'hasAutomaticTwoSidedPrinting', 'colorPagesPerMinute',
            'maximumDocumentSize', 'maximumPrintResolution/unit', 'maximumPrintResolution/measure',
            'maximumScannerResolution/unit', 'maximumScannerResolution/measure', 'monochromeColor',
            'printingTechnology', 'monochromePagesPerMinute', 'wirelessTechnologies/wirelessTechnology'
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
     * Insert VideoGames Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setVideoGames($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'videoGameGenre', 'esrbRating', 'sport/sportValue', 'targetAudience/targetAudienceValue',
            'isOnlineMultiplayerAvailable', 'isDownloadableContentAvailable', 'edition', 'videoGameCollection',
            'requiredPeripherals', 'platform'
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
     * Insert Software Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setSoftware($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'softwareCategory/softwareCategoryValue', 'systemRequirements/systemRequirement', 'version',
            'numberOfUsers', 'softwareFormat', 'requiredPeripherals', 'educationalFocus/educationalFocus',
            'operatingSystem/operatingSystemValue'
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
     * Insert ComputerComponents Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setComputerComponents($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'internalExternal', 'hardDriveCapacity/unit', 'hardDriveCapacity/measure', 'cpuSocketType/unit',
            'cpuSocketType/measure', 'motherboardFormFactor/motherboardFormFactorValue',
            'maximumRamSupported/unit', 'maximumRamSupported/measure', 'processorSpeed/unit',
            'processorSpeed/measure', 'processorType/processorTypeValue', 'ramMemory/unit', 'ramMemory/measure',
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
     * Insert ElectronicsAccessories Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setElectronicsAccessories($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'recordableMediaFormats/recordableMediaFormat', 'compatibleBrands/compatibleBrand',
            'compatibleDevices/compatibleDevice', 'wirelessTechnologies/wirelessTechnology',
            'tvAndMonitorMountType', 'minimumScreenSize/unit', 'minimumScreenSize/measure',
            'maximumScreenSize/unit', 'maximumScreenSize/measure', 'maximumLoadWeight/unit',
            'maximumLoadWeight/measure', 'headphoneFeatures/headphoneFeature'
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
     * Insert Computers Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setComputers($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'operatingSystem/operatingSystemValue', 'hasFrontFacingCamera', 'graphicsInformation',
            'opticalDrive', 'formFactor', 'hasTouchscreen', 'resolution', 'screenSize/unit',
            'screenSize/measure', 'displayTechnology', 'hasBluetooth', 'batteryLife/unit',
            'batteryLife/measure', 'frontFacingCameraMegapixels/unit', 'frontFacingCameraMegapixels/measure',
            'rearCameraMegapixels/unit', 'rearCameraMegapixels/measure', 'hardDriveCapacity/unit',
            'hardDriveCapacity/measure', 'maximumRamSupported/unit', 'maximumRamSupported/measure',
            'processorSpeed/unit', 'processorSpeed/measure', 'processorType/processorTypeValue',
            'ramMemory/unit', 'ramMemory/measure', 'wirelessTechnologies/wirelessTechnology'
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