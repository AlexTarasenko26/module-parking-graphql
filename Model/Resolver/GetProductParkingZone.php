<?php

declare(strict_types=1);

namespace Epam\ParkingGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Epam\Parking\Api\ZoneRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Epam\Parking\Api\Data\ZoneInterface;

class GetProductParkingZone implements ResolverInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ZoneRepositoryInterface $zoneRepository
    ) {
    }

    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $sku = $this->extractSku($args);
        $product = $this->getProductBySku($sku);
        $zoneId = $this->getZoneIdFromProduct($product);

        if (!$zoneId) {
            return null;
        }

        $zone = $this->getZoneById((int)$zoneId);

        return $this->formatZoneData($zone);
    }

    private function extractSku(array $args): string
    {
        $sku = $args['input']['sku'] ?? null;

        if (!$sku) {
            throw new GraphQlInputException(__('Product SKU is required.'));
        }

        return $sku;
    }

    private function getProductBySku(string $sku): ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Product with SKU "%1" not found.', $sku));
        }
    }

    private function getZoneIdFromProduct(ProductInterface $product): ?int
    {
        return (int)($product->getCustomAttribute('zone_id')?->getValue() ?? 0) ?: null;
    }

    private function getZoneById(int $zoneId): ZoneInterface
    {
        try {
            return $this->zoneRepository->getById($zoneId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Parking zone not found for ID %1.', $zoneId));
        }
    }

    private function formatZoneData(ZoneInterface $zone): array
    {
        return [
            'id' => $zone->getId(),
            'name' => $zone->getName(),
            'location' => $zone->getLocation(),
            'max_capacity' => $zone->getMaxCapacity(),
        ];
    }
}
