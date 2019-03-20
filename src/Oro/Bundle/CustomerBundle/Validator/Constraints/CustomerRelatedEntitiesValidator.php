<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdaterInterface;
use Oro\Bundle\EntityBundle\Provider\EntityClassNameProviderInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates Customer User editing when we are trying to change Customer User's Customer, which leads
 * to updating Customer User's related entities (such as Orders, Quotes, Shopping Lists etc) in case the user
 * doesn't have permissions to edit these related entities
 */
class CustomerRelatedEntitiesValidator extends ConstraintValidator
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var CustomerUserReassignUpdaterInterface */
    private $customerUserReassignUpdater;

    /** @var ManagerRegistry */
    private $registry;

    /** @var EntityClassNameProviderInterface */
    private $entityClassNameProvider;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CustomerUserReassignUpdaterInterface $customerUserReassignUpdater
     * @param ManagerRegistry $registry
     * @param EntityClassNameProviderInterface $entityClassNameProvider
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CustomerUserReassignUpdaterInterface $customerUserReassignUpdater,
        ManagerRegistry $registry,
        EntityClassNameProviderInterface $entityClassNameProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->customerUserReassignUpdater = $customerUserReassignUpdater;
        $this->registry = $registry;
        $this->entityClassNameProvider = $entityClassNameProvider;
    }

    /**
     * @param CustomerUser $customerUser
     * @param CustomerRelatedEntities $constraint
     */
    public function validate($customerUser, Constraint $constraint)
    {
        if (!$customerUser instanceof CustomerUser) {
            throw new UnexpectedTypeException($customerUser, CustomerUser::class);
        }

        if (!$customerUser->getId()) {
            return;
        }

        $em = $this->registry->getManagerForClass(CustomerUser::class);

        /** @var array $originalCustomerUser */
        $originalCustomerUser = $em->getUnitOfWork()
            ->getOriginalEntityData($customerUser);

        if (!isset($originalCustomerUser['customer'])
            || $originalCustomerUser['customer'] === $customerUser->getCustomer()
        ) {
            return;
        }

        $entityClassesToUpdate = $this->customerUserReassignUpdater->getClassNamesToUpdate(
            $customerUser
        );

        $restrictions = [];
        foreach ($entityClassesToUpdate as $entityClass) {
            if (!$this->authorizationChecker->isGranted(sprintf(
                '%s;entity:%s',
                'EDIT',
                $entityClass
            ))) {
                $restrictions[] = $entityClass;
            }
        }

        array_walk($restrictions, function (&$className) {
            $className = $this->entityClassNameProvider->getEntityClassName($className);
        });

        if ($restrictions) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ entityNames }}', implode(', ', $restrictions))
                ->atPath('customer')
                ->addViolation();
        }
    }
}
