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


class ToolsAndHardware extends \Ced\Fruugo\Helper\Product\Base
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
                'swatchImages/swatchImage/swatchVariantAttribute', 'accessoriesIncluded/accessoriesIncludedValue',
                'variantGroupId', 'variantAttributeNames/variantAttributeName', 'isPrimaryVariant',
                'isWeatherResistant', 'isFireResistant', 'brand', 'manufacturer', 'color/colorValue',
                'material/materialValue', 'numberOfPieces', 'cleaningCareAndMaintenance',
                'recommendedUses/recommendedUse', 'isIndustrial', 'isWaterproof', 'shape'
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
                case 'PlumbingAndHVAC' : {
                    $data['PlumbingAndHVAC'] = $this->setPlumbingAndHVAC($product, $attributes);
                    break;
                    }
                case 'Hardware' : {
                    $data['Hardware'] = $this->setHardware($product, $attributes);
                    break;
                    }
                case 'BuildingSupply' : {
                    $data['BuildingSupply'] = $this->setBuildingSupply($product, $attributes);
                    break;
                    }
                case 'Tools' : {
                    $data['Tools'] = $this->setTools($product, $attributes);
                    break;
                    }
                case 'Electrical' : {
                    $data['Electrical'] = $this->setElectrical($product, $attributes);
                    break;
                    }
            }
        }
        return $data;
    }

    /**
     * Insert PlumbingAndHVAC Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setPlumbingAndHVAC($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'isEnergyGuideLabelRequired', 'energyGuideLabel', 'finish', 'homeDecorStyle',
            'mountType/mountTypeValue', 'powerType', 'isRemoteControlIncluded', 'seatingCapacity',
            'volumeCapacity/unit', 'volumeCapacity/measure', 'fuelType', 'volts/unit', 'volts/measure',
            'watts/unit', 'watts/measure', 'btu', 'maximumRoomSize/unit', 'maximumRoomSize/measure',
            'hasAutomaticShutoff', 'hasCeeCertification', 'ceeTier', 'drainConfiguration', 'faucetDrillings',
            'gallonsPerFlush/unit', 'gallonsPerFlush/measure', 'gallonsPerMinute/unit',
            'gallonsPerMinute/measure', 'humidificationOutputPerDay', 'inletDiameter/unit',
            'inletDiameter/measure', 'mervRating', 'outletDiameter/unit', 'outletDiameter/measure',
            'pintsOfMoistureRemovedPerDay', 'spoutHeight/unit', 'spoutHeight/measure', 'spoutReach/unit',
            'spoutReach/measure', 'spudInletSize/unit', 'spudInletSize/measure', 'threadStandard',
            'toiletBowlSize', 'tripLeverPlacement', 'isVented', 'ventingRequired', 'humidificationMethod',
            'horsepower/unit', 'horsepower/measure'
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
     * Insert Hardware Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setHardware($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'finish', 'homeDecorStyle', 'mountType/mountTypeValue', 'maximumWeight/unit', 'maximumWeight/measure',
            'backsetSize/unit', 'backsetSize/measure', 'liftHeight/unit', 'liftHeight/measure', 'isLockable',
            'maximumForceResisted/unit', 'maximumForceResisted/measure', 'petSize', 'threadStandard'
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
     * Insert BuildingSupply Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setBuildingSupply($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'homeDecorStyle',
            'acRating', 'batteriesRequired', 'batterySize', 'isBiodegradable', 'isEnergyStarCertified',
            'carpetStyle', 'pattern/patternValue', 'isPowered', 'powerType', 'isCombustible',
            'compatibleSurfaces/compatibleSurface', 'coverageArea/unit', 'coverageArea/measure',
            'isMadeFromRecycledMaterial', 'dryTime/unit', 'dryTime/measure',
            'recycledMaterialContent/recycledMaterialContentValue/recycledMaterial',
            'recycledMaterialContent/recycledMaterialContentValue/percentageOfRecycledMaterial',
            'isFastSetting', 'fineness', 'isFlammable', 'grade', 'hasLowEmissivity',
            'isMadeFromReclaimedMaterials', 'isMadeFromSustainableMaterials', 'isMoldResistant', 'isOdorless',
            'paintFinish', 'peiRating', 'pileHeight/unit', 'pileHeight/measure', 'isPrefinished',
            'isReadyToUse', 'recommendedSurfaces/recommendedSurface', 'rollLength/unit', 'rollLength/measure',
            'snowLoadRating/unit', 'snowLoadRating/measure', 'vocLevel/unit', 'vocLevel/measure',
            'isWaterSoluble', 'subject', 'activeIngredients/activeIngredient/activeIngredientName',
            'activeIngredients/activeIngredient/activeIngredientPercentage',
            'inactiveIngredients/inactiveIngredient', 'form', 'hasCeeCertification', 'ceeTier'
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
     * Insert Tools Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setTools($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'finish',
            'batteriesRequired', 'batterySize', 'powerType', 'isPortable', 'hasCfl',
            'isLightingFactsLabelRequired', 'lightingFactsLabel', 'volumeCapacity/unit',
            'volumeCapacity/measure', 'fuelType', 'volts/unit', 'volts/measure', 'cordLength/unit',
            'cordLength/measure', 'lightBulbType', 'handing', 'caseIncluded', 'amps/unit', 'amps/measure',
            'isBareTool', 'batteryCapacity/unit', 'batteryCapacity/measure', 'chargerIncluded',
            'chargingTime/unit', 'chargingTime/measure', 'hasElectricBrake', 'isVariableSpeed',
            'toolFreeBladeChanging', 'bladeDiameter/unit', 'bladeDiameter/measure', 'bladeLength/unit',
            'bladeLength/measure', 'bladeShank', 'teethPerInch', 'discSize/unit', 'discSize/measure',
            'chuckSize/unit', 'chuckSize/measure', 'chuckType', 'colletSize/unit', 'colletSize/measure',
            'sandingBeltSize', 'arborDiameter/unit', 'arborDiameter/measure', 'spindleThread', 'shankSize/unit',
            'shankSize/measure', 'shankShape', 'maximumJawOpening/unit', 'maximumJawOpening/measure',
            'decibelRating/unit', 'decibelRating/measure', 'impactEnergy/unit', 'impactEnergy/measure',
            'blowsPerMinute', 'strokeLength/unit', 'strokeLength/measure', 'strokesPerMinute',
            'maximumWattsOut/unit', 'maximumWattsOut/measure', 'noLoadSpeed/unit', 'noLoadSpeed/measure',
            'torque', 'sandingSpeed/unit', 'sandingSpeed/measure', 'airInlet/unit', 'airInlet/measure',
            'averageAirConsumptionAt90PSI/unit', 'averageAirConsumptionAt90PSI/measure', 'cfmAt40Psi/unit',
            'cfmAt40Psi/measure', 'cfmAt90Psi/unit', 'cfmAt90Psi/measure', 'workingPressure/unit',
            'workingPressure/measure', 'maximumAirPressure/unit', 'maximumAirPressure/measure',
            'tankConfiguration', 'tankSize/unit', 'tankSize/measure', 'isCarbCompliant',
            'engineDisplacement/unit', 'engineDisplacement/measure', 'horsepower/measure', 'engineStarter',
            'hasAutomaticTransferSwitch', 'clearingWidth', 'loadCapacity'
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
     * Insert Electrical Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setElectrical($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'finish',
            'homeDecorStyle', 'batteriesRequired', 'mountType', 'batterySize', 'isEnergyStarCertified',
            'pattern/patternValue', 'character/characterValue', 'powerType', 'diameter/unit',
            'diameter/measure', 'hasCfl', 'isLightingFactsLabelRequired', 'lightingFactsLabel', 'volts/unit',
            'volts/measure', 'watts/unit', 'watts/measure', 'estimatedEnergyCostPerYear/unit',
            'estimatedEnergyCostPerYear/measure', 'colorTemperature/unit', 'colorTemperature/measure',
            'numberOfLightBulbs', 'lightBulbBaseType', 'lightBulbDiameter/unit', 'lightBulbDiameter/measure',
            'isLightBulbIncluded', 'beamAngle/unit', 'beamAngle/measure', 'beamSpread/unit',
            'beamSpread/measure', 'compatibleConduitSizes/unit', 'compatibleConduitSizes/measure',
            'isDarkSkyCompliant', 'electricalBallastFactor', 'isRatedForOutdoorUse', 'maximumEnergySurgeRating',
            'maximumRange/unit', 'maximumRange/measure', 'responseTime/unit', 'responseTime/measure',
            'numberOfGangs', 'numberOfPoles', 'americanWireGauge/unit', 'americanWireGauge/measure',
            'brightness/unit', 'brightness/measure', 'lifespan', 'hasCeeCertification', 'ceeTier', 'amps/unit',
            'amps/measure', 'decibelRating/unit', 'decibelRating/measure', 'horsepower/unit',
            'horsepower/measure'
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