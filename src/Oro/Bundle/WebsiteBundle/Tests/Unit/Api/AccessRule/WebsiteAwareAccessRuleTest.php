<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Api\AccessRule;

use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\WebsiteBundle\Acl\AccessRule\WebsiteAwareAccessRule;

class WebsiteAwareAccessRuleTest extends \PHPUnit\Framework\TestCase
{
    public function testIsApplicableWithoutWebsite()
    {
        /** @var Criteria|\PHPUnit\Framework\MockObject\MockObject $criteria */
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects($this->once())
            ->method('hasOption')
            ->with('websiteId')
            ->willReturn(false);

        $accessRule = new WebsiteAwareAccessRule();
        $this->assertFalse($accessRule->isApplicable($criteria));
    }

    public function testIsApplicableWithWebsite()
    {
        /** @var Criteria|\PHPUnit\Framework\MockObject\MockObject $criteria */
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects($this->once())
            ->method('hasOption')
            ->with('websiteId')
            ->willReturn(true);

        $accessRule = new WebsiteAwareAccessRule();
        $this->assertTrue($accessRule->isApplicable($criteria));
    }

    public function testProcess()
    {
        /** @var Criteria|\PHPUnit\Framework\MockObject\MockObject $criteria */
        $criteria = $this->createMock(Criteria::class);
        $criteria->expects($this->once())
            ->method('getOption')
            ->with('websiteId')
            ->willReturn(123);
        $criteria->expects($this->once())
            ->method('andExpression')
            ->with(new Comparison(new Path('website', $criteria->getAlias()), Comparison::EQ, 123))
            ->willReturnSelf();

        $accessRule = new WebsiteAwareAccessRule();
        $accessRule->process($criteria);
    }
}
