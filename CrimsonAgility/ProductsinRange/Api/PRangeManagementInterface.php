<?php
declare(strict_types=1);

namespace CrimsonAgility\ProductsinRange\Api;

interface PRangeManagementInterface
{

    /**
     * POST for Product Range api
     * @param int $low
     * @param int $high
     * @param string $sort
     * @param int $limit
     * @return json
     */
    public function postPRange($low, $high, $sort = '', $limit = 10);
}

