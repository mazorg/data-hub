<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Bundle\DataHubBundle\GraphQL\DocumentElementType;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Pimcore\Bundle\DataHubBundle\GraphQL\ElementDescriptor;
use Pimcore\Bundle\DataHubBundle\GraphQL\Service;
use Pimcore\Bundle\DataHubBundle\GraphQL\SharedType\HotspotCropType;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Tag\Image;

class ImageType extends ObjectType
{
    protected static $instance;

    /**
     * @param mixed[] $config
     */
    public static function getInstance(Service $graphQlService)
    {

        if (!self::$instance) {

            $resolver = new \Pimcore\Bundle\DataHubBundle\GraphQL\Resolver\HotspotType();
            $resolver->setGraphQLService($graphQlService);

            $assetType = $graphQlService->buildAssetType("asset");
            $hotspotMarkerType = $graphQlService->buildGeneralType("hotspotmarker");
            $hotspotHotspotType = $graphQlService->buildGeneralType("hotspothotspot");

            $config =
                [
                    'name' => 'document_tagImage',
                    'fields' => [
                        '__tagType' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Image) {
                                    return $value->getType();
                                }
                            }
                        ],
                        '__tagName' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Image) {
                                    return $value->getName();
                                }
                            }
                        ]
                        ,
                        'image' => [
                            'type' => $assetType,
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) use ($resolver) {
                                if ($value instanceof Image) {
                                    $data = $value->getData();
                                    if (isset($data['id'])) {
                                        $data = new ElementDescriptor(Asset::getById($data['id']));
                                        $result = $resolver->resolveImage($data, $args, $context, $resolveInfo);
                                        return $result;
                                    }
                                }
                            }
                        ],
                        'alt' => [
                            'type' => Type::string(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) use ($resolver) {
                                if ($value instanceof Image) {
                                    return $value->getAlt();
                                }
                            }
                        ],
                        'crop' => [
                            'type' => HotspotCropType::getInstance(),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) use ($resolver) {
                                if ($value instanceof Image) {
                                    return [
                                        'cropTop' => $value->getCropTop(),
                                        'cropLeft' => $value->getCropLeft(),
                                        'cropHeight' => $value->getCropHeight(),
                                        'cropWidth' => $value->getCropWidth(),
                                        'cropPercent' => $value->getCropPercent()
                                    ];
                                }
                            }
                        ],
                        'hotspots' => [
                            'type' => Type::listOf($hotspotHotspotType),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) use ($resolver) {
                                if ($value instanceof Image) {
                                    return $value->getHotspots();
                                }
                            }
                        ],
                        'marker' => [
                            'type' => Type::listOf($hotspotMarkerType),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) use ($resolver) {
                                if ($value instanceof Image) {
                                    return $data = $value->getMarker();
                                }
                            }
                        ],
                    ]
                ];
            self::$instance = new static($config);
        }

        return self::$instance;
    }


}
