<?php

namespace Ced\Fruugo\Helper\Validator;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
//    public $_idAttributes = [
//        '/table/row' => 'id',
//        '/table/row/column' => 'id',
//    ];


    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Config\ConverterInterface $converter,
        \Ced\Fruugo\Helper\Validator\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'MPProduct.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}