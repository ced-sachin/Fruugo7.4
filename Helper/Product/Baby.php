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


class Baby extends \Ced\Fruugo\Helper\Product\Base
{
    /**
     * Insert Baby Category Data
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
                'swatchImages/swatchImage/swatchImageUrl', 'swatchImages/swatchImage/swatchVariantAttribute',
                'ageRange', 'minimumWeight/measure', 'variantAttributeNames/variantAttributeName', 'variantGroupId',
                'isPrimaryVariant', 'fabricContent/fabricContentValue',
                'fabricCareInstructions/fabricCareInstruction', 'brand', 'condition', 'manufacturer', 'modelNumber',
                'manufacturerPartNumber', 'gender', 'color/colorValue', 'ageGroup', 'isReusable', 'isDisposable',
                'pattern/patternValue', 'material/materialValue', 'numberOfPieces', 'character/characterValue',
                'occasion/occasionValue', 'isPersonalizable', 'isPortable', 'isMadeFromRecycledMaterial',
                'recycledMaterialContent/recycledMaterialContentValue/recycledMaterial',
                'recycledMaterialContent/recycledMaterialContentValue/percentageOfRecycledMaterial',
                'recommendedUses/recommendedUse', 'numberOfChannels', 'isFairTrade', 'maximumRecommendedAge/unit',
                'maximumRecommendedAge/measure', 'minimumRecommendedAge/unit', 'minimumRecommendedAge/measure',
                'sport/sportValue', 'maximumWeight/unit', 'maximumWeight/measure',
                'diaposableBabyDiaperType/diaposableBabyDiaperTypeValue', 'capacity', 'scent',
                'organicCertifications/organicCertification', 'screenSize/unit',  'screenSize/measure',
                'displayTechnology'
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
                case 'ChildCarSeats' : {
                    $data['ChildCarSeats'] = $this->setChildCarSeats($product, $attributes);
                    break;
                    }
                case 'BabyClothing' : {
                    $data['BabyClothing'] = $this->setBabyClothing($product, $attributes);
                    break;
                    }
                case 'BabyFootwear' : {
                    $data['BabyFootwear'] = $this->setBabyFootwear($product, $attributes);
                    break;
                    }
                case 'Strollers' : {
                    $data['Strollers'] = $this->setStrollers($product, $attributes);
                    break;
                    }
                case 'BabyFurniture' : {
                    $data['BabyFurniture'] = $this->setBabyFurniture($product, $attributes);
                    break;
                    }
                case 'BabyToys' : {
                    $data['BabyToys'] = $this->setBabyToys($product, $attributes);
                    break;
                    }
                case 'BabyFood' : {
                    $data['BabyFood'] = $this->setBabyFood($product, $attributes);
                    break;
                    }
            }
        }
        return $data;
    }

    /**
     * Insert ChildCarSeats Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setChildCarSeats($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'childCarSeatType', 'facingDirection', 'forwardFacingMinimumWeight/unit',
            'forwardFacingMinimumWeight/measure', 'forwardFacingMaximumWeight/unit',
            'forwardFacingMaximumWeight/measure', 'rearFacingMinimumWeight/unit',
            'rearFacingMinimumWeight/measure', 'rearFacingMaximumWeight/unit',
            'rearFacingMaximumWeight/measure', 'hasLatchSystem'
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
     * Insert BabyClothing Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setBabyClothing($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'color/colorValue', 'apparelCategory', 'season/seasonValue', 'babyClothingSize'
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
     * Insert BabyFootwear Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setBabyFootwear($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'shoeCategory', 'shoeSize', 'shoeWidth', 'shoeStyle', 'shoeClosure'
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
     * Insert Strollers Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setStrollers($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'seatingCapacity', 'strollerType'
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
     * Insert BabyFurniture Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setBabyFurniture($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'collection', 'finish', 'homeDecorStyle', 'isWheeled', 'isFoldable', 'isAssemblyRequired',
            'assemblyInstructions', 'mattressFirmness', 'fillMaterial/fillMaterialValue', 'bedSize', 'shape'
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
     * Insert BabyToys Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setBabyToys($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'animalBreed', 'ageRange', 'theme/themeValue', 'batteriesRequired', 'batterySize',
            'awardsWon/awardsWonValue', 'animalType', 'isPowered', 'powerType', 'isRemoteControlIncluded',
            'makesNoise', 'fillMaterial/fillMaterialValue', 'educationalFocus/educationalFocus'
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
     * Insert BabyFood Category Data
     * @param string|[] $product
     * @param string|[] $attributes
     * @return string|[]
     */
    public function setBabyFood($product = [], $attributes = [])
    {
        $fruugoAttr = [
            'flavor', 'nutrientContentClaims/nutrientContentClaim', 'servingSize', 'servingsPerContainer',
            'isBabyFormulaLabelRequired', 'babyFormulaLabel', 'isChildrenUnder2LabelRequired',
            'childrenUnder2Label', 'isChildrenUnder4LabelRequired', 'childrenUnder4Label',
            'fluidOuncesSupplying100Calories', 'calories', 'caloriesFromFat/unit', 'caloriesFromFat/measure',
            'totalFat/unit', 'totalFat/measure', 'totalFatPercentageDailyValue/unit',
            'totalFatPercentageDailyValue/measure', 'totalCarbohydrate/unit', 'totalCarbohydrate/measure',
            'totalCarbohydratePercentageDailyValue/value', 'totalCarbohydratePercentageDailyValue/measure',
            'nutrients/nutrient/nutrientName', 'nutrients/nutrient/nutrientAmount',
            'nutrients/nutrient/nutrientPercentageDailyValue',
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