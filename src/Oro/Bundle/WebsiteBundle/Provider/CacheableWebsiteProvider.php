<?php

namespace Oro\Bundle\WebsiteBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class CacheableWebsiteProvider implements WebsiteProviderInterface
{
    const WEBSITE_IDS_CACHE_KEY = 'oro_website_entity_ids';

    /** @var WebsiteProviderInterface */
    private $websiteProvider;

    /** @var CacheProvider */
    private $cacheProvider;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /**
     * @param WebsiteProviderInterface $websiteProvider
     * @param CacheProvider $cacheProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        WebsiteProviderInterface $websiteProvider,
        CacheProvider $cacheProvider,
        DoctrineHelper $doctrineHelper
    ) {
        $this->websiteProvider = $websiteProvider;
        $this->cacheProvider = $cacheProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        $websites = [];
        foreach ($this->getWebsiteIds() as $websiteId) {
            $websites[$websiteId] = $this->doctrineHelper->getEntityReference(Website::class, $websiteId);
        }
        return $websites;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        if (!$this->cacheProvider->contains(self::WEBSITE_IDS_CACHE_KEY)) {
            $websiteIds = $this->websiteProvider->getWebsiteIds();

            $this->cacheProvider->save(self::WEBSITE_IDS_CACHE_KEY, $websiteIds);
        } else {
            $websiteIds = $this->cacheProvider->fetch(self::WEBSITE_IDS_CACHE_KEY);
        }

        return $websiteIds;
    }

    /**
     * Checks if this provider has data in the internal cache.
     *
     * @return bool
     */
    public function hasCache()
    {
        return $this->cacheProvider->contains(self::WEBSITE_IDS_CACHE_KEY);
    }

    /**
     * Removes all data from the internal cache.
     */
    public function clearCache()
    {
        $this->cacheProvider->delete(self::WEBSITE_IDS_CACHE_KEY);
    }
}
