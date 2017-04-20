<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class CustomerUserFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'Jerry Coleman':
                $owner = (new User())->setId(1);
                $dateTime = (new \DateTime())->setTimestamp(545843181);
                $customerRole = $this->generateCustomerRole();
                $website = $this->generateWebsite();

                $entity->setCustomer((new Customer())->setName('Oro Inc.'))
                    ->setFirstName('Jerry')
                    ->setLastName('Coleman')
                    ->setEmail('example@email.com')
                    ->setConfirmed(false)
                    ->setEnabled(true)
                    ->setOwner($owner)
                    ->setWebsite($website)
                    ->setBirthday($dateTime)
                    ->setNamePrefix('Mr')
                    ->setNameSuffix('Jr.')
                    ->setNameSuffix('Jr.')
                    ->setMiddleName('John');

                $this->addRole($entity, $customerRole);

                return;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Jerry Coleman');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new CustomerUser();
    }

    /**
     * @return Website
     */
    private function generateWebsite()
    {
        $website = new Website();
        $reflectionObject = new \ReflectionObject($website);
        $property = $reflectionObject->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($website, 1);
        $property->setAccessible(false);

        return $website;
    }

    /**
     * @return CustomerUserRole
     */
    private function generateCustomerRole()
    {
        $customerRole = new CustomerUserRole();
        $reflectionObject = new \ReflectionObject($customerRole);
        $property = $reflectionObject->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($customerRole, 1);
        $property->setAccessible(false);

        return $customerRole;
    }

    /**
     * @param $entity
     * @param $customerRole
     */
    private function addRole($entity, $customerRole)
    {
        $reflectionObject = new \ReflectionObject($entity);
        $property = $reflectionObject->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($entity, new ArrayCollection([$customerRole]));
        $property->setAccessible(false);
    }
}
