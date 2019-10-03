<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CMSBundle\Form\Type\WYSIWYGType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Form\Extension\WYSIWYGTypeExtension;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Layout\Extension\Theme\DataProvider\ThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WYSIWYGTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ThemeManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeManager;

    /**
     * @var ThemeProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeProvider;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configManager;

    /**
     * @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManager;

    /**
     * @var WYSIWYGTypeExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->themeProvider = $this->createMock(ThemeProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->extension = new WYSIWYGTypeExtension(
            $this->themeManager,
            $this->themeProvider,
            $this->configManager,
            $this->websiteManager
        );
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([WYSIWYGType::class], $this->extension::getExtendedTypes());
    }

    public function testConfigureOptions()
    {
        /* @var $resolver OptionsResolver|\PHPUnit\Framework\MockObject\MockObject */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefault')
            ->with('page-component', function () {
            })
            ->will($this->returnSelf());

        $this->extension->configureOptions($resolver);
    }
}
