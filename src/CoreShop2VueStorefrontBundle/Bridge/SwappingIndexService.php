<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use ONGR\ElasticsearchBundle\Service\IndexService;

class SwappingIndexService extends IndexService
{
    private $runTimestamp;
    /**
     * @var IndexService
     */
    private $baseIndexService;

    public function __construct(IndexService $baseIndexService, string $runTimestamp)
    {
        $this->baseIndexService = $baseIndexService;
        $this->runTimestamp = $runTimestamp;
    }

    /**
     * Flushes ES and adds extra steps needed for swapping (assign alias to new index, remove from old indices, drop old indices)
     *
     * @param array $params
     * @return array
     */
    public function flush(array $params = []): array
    {
        $return = $this->baseIndexService->flush($params);

        $indicesClient = $this->baseIndexService->getClient()->indices();
        $settings = $this->baseIndexService->indexSettings;

        $oldIndices = null;
        // if alias doesn't exist, the previous index will have a clashing name with alias we're creating so we have to drop it
        if(!$indicesClient->existsAlias(['name' => $settings->getAlias()])) {
            $this->baseIndexService->dropIndex();
        }
        else {
            //get index names associated with alias
            $oldIndices = implode(',', array_keys($indicesClient->getAlias(['name' => $settings->getAlias()])));
            //delete alias from old indices
            $indicesClient->deleteAlias([
                "index" => $oldIndices,
                "name" => $settings->getAlias()
            ]);
        }
        //assign alias to new index
        $indicesClient->putAlias([
            "index" => $settings->getIndexName(),
            "name" => $settings->getAlias()
        ]);

        if($oldIndices) {
            //drop old indices
            $indicesClient->delete([
                "index" => $oldIndices
            ]);
        }

        return $return;
    }

    public function getBaseIndexService(): IndexService
    {
        return $this->baseIndexService;
    }

    public function __call($method, $args)
    {
        if (!method_exists($this, $method) && method_exists($this->baseIndexService, $method)) {
            return $this->baseIndexService->$method($args);
        }
    }
}