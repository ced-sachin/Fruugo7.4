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

class Vehicle extends \Ced\Fruugo\Helper\Product\Base
{
    /**
     * Insert Vehicle Category Data
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
                'variantGroupId',  'variantAttributeNames/variantAttributeName', 'isPrimaryVariant', 'brand',
                'condition','manufacturer', 'modelNumber', 'manufacturerPartNumber', 'color/colorValue',
                'material/materialValue'
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
                case 'WheelsAndWheelComponents' : {
                    $data['WheelsAndWheelComponents'] = $this->setWheelsAndWheelComponents($product, $attributes);
                    break;
                    }
                case 'LandVehicles' : {
                    $data['LandVehicles'] = $this->setLandVehicles($product, $attributes);
                    break;
                    }
                case 'VehiclePartsAndAccessories' : {
                    $data['VehiclePartsAndAccessories'] = $this->setVehiclePartsAndAccessories($product, $attributes);
                    break;
                    }
                case 'Tires' : {
                    $data['Tires'] = $this->setTires($product, $attributes);
                    break;
                    }
                case 'Watercraft' : {
                    $data['Watercraft'] = $this->setWatercraft($product, $attributes);
                    break;
                    }
            }
        }
        return $data;
    }

    /**
     * Insert WheelsAndWheelComponents Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setWheelsAndWheelComponents($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'finish', 'diameter/unit', 'diameter/measure', 'compatibleTireSize', 'numberOfSpokes',
            'hasWearSensor'
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
     * Insert LandVehicles Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setLandVehicles($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'landVehicleCategory', 'powertrain', 'drivetrain', 'transmissionDesignation', 'acceleration',
            'frontSuspension', 'rearSuspension', 'frontBrakes', 'rearBrakes', 'seatingCapacity', 'frontWheels',
            'rearWheels', 'frontTires', 'rearTires', 'wheelbase/unit', 'wheelbase/measure', 'curbWeight/unit',
            'curbWeight/measure', 'towingCapacity/unit', 'towingCapacity/measure', 'submodel', 'seatHeight/unit',
            'seatHeight/measure', 'engineModel', 'compressionRatio', 'boreStroke', 'inductionSystem',
            'coolingSystem', 'maximumEnginePower', 'topSpeed', 'fuelRequirement', 'fuelSystem',
            'fuelCapacity/unit', 'fuelCapacity/measure', 'averageFuelConsumption/unit',
            'averageFuelConsumption/measure', 'vehicleMake', 'vehicleModel', 'vehicleType', 'vehicleYear',
            'torque', 'engineDisplacement/unit', 'engineDisplacement/measure'
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
     * Insert VehiclePartsAndAccessories Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setVehiclePartsAndAccessories($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'fabricContent/fabricContentValue', 'isWeatherResistant', 'finish', 'chainLength/unit',
            'chainLength/measure', 'fabricCareInstructions/fabricCareInstruction', 'batteriesRequired',
            'batterySize', 'isReusable', 'connections/connection', 'character/characterValue', 'powerType',
            'tireDiameter/unit', 'tireDiameter/measure', 'fillMaterial/fillMaterialValue', 'fluidOunces/unit',
            'fluidOunces/measure', 'maximumTemperature/unit', 'maximumTemperature/measure',
            'volumeCapacity/unit', 'volumeCapacity/measure', 'fuelType', 'volts/unit', 'volts/measure',
            'watts/unit', 'watts/measure', 'isLightBulbIncluded', 'vehicleMake', 'beamAngle/unit',
            'beamAngle/measure', 'beamSpread/unit', 'beamSpread/measure', 'vehicleModel', 'vehicleType',
            'vehicleYear', 'automotiveWindowShadeFit', 'breakingStrength/unit', 'breakingStrength/measure',
            'candlePower', 'displayResolution/unit', 'displayResolution/measure', 'form', 'coldCrankAmp',
            'compatibleCars', 'dropDistance/unit','dropDistance/measure', 'shape', 'fastenerHeadType',
            'isLockable', 'filterLife/unit', 'filterLife/measure', 'flashPoint', 'fullyIncinerable',
            'hitchClass', 'inDashSystem', 'interfaceType/interfaceTypeValue', 'displayTechnology',
            'maximumMotorSpeed', 'numberOfOutlets', 'numberOfPhases', 'receiverCompatibility/unit',
            'receiverCompatibility/measure', 'reserveCapacity/unit', 'reserveCapacity/measure', 'saeDotCompliant',
            'shackleClearance/unit', 'shackleClearance/measure', 'shackleDiameter/unit',
            'shackleDiameter/measure', 'shackleLength/unit', 'shackleLength/measure', 'shankLength/unit',
            'shankLength/measure', 'shearStrength/unit', 'shearStrength/measure',
            'hasShortCircuitProtection/unit', 'hasShortCircuitProtection/measure',
            'thickness/unit', 'thickness/measure', 'threadSize/unit', 'threadSize/measure', 'towingMirrorSide',
            'lightBulbType', 'cableLength/unit', 'cableLength/measure', 'compatibleBrands/compatibleBrand',
            'compatibleDevices/compatibleDevice', 'wirelessTechnologies/wirelessTechnology', 'amps/unit',
            'amps/measure', 'maximumLoadWeight/unit', 'maximumLoadWeight/measure', 'horsepower/unit',
            'horsepower/measure', 'loadCapacity/unit', 'loadCapacity/measure'
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
     * Insert Tires  Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setTires($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'tireDiameter/unit', 'tireDiameter/measure', 'tireSize', 'tireWidth', 'tireSeason', 'tireLoadIndex',
            'tireSpeedRating', 'tireTreadwearRating', 'isRunFlat', 'tireTractionRating', 'tireTemperatureRating',
            'constructionType', 'tireSidewallStyle', 'tireType', 'maximumInflationPressure/unit',
            'maximumInflationPressure/measure', 'treadDepth', 'treadWidth', 'uniformTireQualityGrade',
            'overallDiameter/unit', 'overallDiameter/measure'
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
     * Insert Watercraft Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setWatercraft($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'seatingCapacity', 'watercraftCategory', 'submodel', 'engineLocation', 'propulsionSystem',
            'engineModel', 'compressionRatio', 'boreStroke', 'inductionSystem', 'coolingSystem',
            'maximumEnginePower', 'thrust/unit', 'thrust/measure', 'impellerPropeller', 'topSpeed',
            'fuelRequirement' , 'fuelSystem', 'fuelCapacity/unit',  'fuelCapacity/measure',
            'averageFuelConsumption/unit', 'averageFuelConsumption/measure', 'hullLength/unit',
            'hullLength/measure', 'beam/unit', 'beam/measure', 'airDraft/unit', 'airDraft/measure', 'draft/unit',
            'draft/measure', 'waterCapacity/unit', 'waterCapacity/measure', 'dryWeight/unit', 'dryWeight/measure',
            'vehicleMake', 'vehicleModel', 'vehicleType', 'vehicleYear', 'engineDisplacement'
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