<?php

declare(strict_types=1);

namespace Epam\ParkingGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class Identity implements IdentityInterface
{
    private string $cacheTag = "parking_zone";

    public function getIdentities(array $resolvedData): array
    {
        return [ $this->cacheTag ];
    }
}
