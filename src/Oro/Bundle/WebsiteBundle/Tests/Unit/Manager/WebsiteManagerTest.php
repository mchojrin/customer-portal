<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\PlatformBundle\Maintenance\Mode;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class WebsiteManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var WebsiteManager
     */
    protected $manager;

    /**
     * @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendHelper;

    /**
     * @var Mode|\PHPUnit\Framework\MockObject\MockObject
     */
    private $maintenance;

    public function setUp()
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->maintenance = $this->createMock(Mode::class);

        $this->manager = new WebsiteManager($this->managerRegistry, $this->frontendHelper, $this->maintenance);
    }

    public function tearDown()
    {
        unset($this->managerRegistry, $this->manager, $this->frontendHelper);
    }

    public function testGetCurrentWebsite()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $repository = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $website = new Website();
        $repository->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        $this->assertSame($website, $this->manager->getCurrentWebsite());
    }

    public function testGetDefaultWebsite()
    {
        $this->frontendHelper->expects($this->never())
            ->method('isFrontendRequest');

        $repository = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $website = new Website();
        $repository->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with(Website::class)
            ->willReturn($repository);

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Website::class)
            ->willReturn($objectManager);

        $this->assertSame($website, $this->manager->getDefaultWebsite());
    }

    public function testGetCurrentWebsiteNonFrontend()
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->managerRegistry->expects($this->never())
            ->method('getManagerForClass');

        $this->assertNull($this->manager->getCurrentWebsite());
    }

    public function testSetCurrentWebsite()
    {
        $this->manager->setCurrentWebsite($website = $this->createMock(Website::class));

        $this->assertSame($website, $this->manager->getCurrentWebsite());
    }

    public function testOnClear()
    {
        $website = new Website();

        $propertyReflection = new \ReflectionProperty(get_class($this->manager), 'currentWebsite');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->manager, $website);

        $this->assertAttributeNotEmpty('currentWebsite', $this->manager);
        $this->manager->onClear();
        $this->assertAttributeEmpty('currentWebsite', $this->manager);
    }

    public function testGetCurrentWebsiteWhenMaintenanceMode(): void
    {
        $this->maintenance
            ->expects($this->once())
            ->method('isOn')
            ->willReturn('true');

        $this->managerRegistry
            ->expects($this->never())
            ->method('getManagerForClass');

        $this->assertNull($this->manager->getCurrentWebsite());
    }
}
