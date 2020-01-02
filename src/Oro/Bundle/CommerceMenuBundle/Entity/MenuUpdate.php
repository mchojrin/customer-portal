<?php

namespace Oro\Bundle\CommerceMenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CommerceMenuBundle\Model\ExtendMenuUpdate;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateTrait;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

/**
 * Commerce Menu Update entity
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository")
 * @ORM\Table(
 *      name="oro_commerce_menu_upd",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="oro_commerce_menu_upd_uidx",
 *              columns={"key", "scope_id", "menu"}
 *          )
 *      }
 * )
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(
 *          name="titles",
 *          joinTable=@ORM\JoinTable(
 *              name="oro_commerce_menu_upd_title",
 *              joinColumns={
 *                  @ORM\JoinColumn(
 *                      name="menu_update_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE"
 *                  )
 *              },
 *              inverseJoinColumns={
 *                  @ORM\JoinColumn(
 *                      name="localized_value_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE",
 *                      unique=true
 *                  )
 *              }
 *          )
 *      ),
 *      @ORM\AssociationOverride(
 *          name="descriptions",
 *          joinTable=@ORM\JoinTable(
 *              name="oro_commerce_menu_upd_descr",
 *              joinColumns={
 *                  @ORM\JoinColumn(
 *                      name="menu_update_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE"
 *                  )
 *              },
 *              inverseJoinColumns={
 *                  @ORM\JoinColumn(
 *                      name="localized_value_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE",
 *                      unique=true
 *                  )
 *              }
 *          )
 *      )
 * })
 * @Config(
 *      routeName="oro_commerce_menu_global_menu_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-th"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class MenuUpdate extends ExtendMenuUpdate implements
    MenuUpdateInterface
{
    use MenuUpdateTrait {
        MenuUpdateTrait::__construct as traitConstructor;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="`condition`", type="string", length=512, nullable=true)
     */
    protected $condition;

    /**
     * @var Collection|MenuUserAgentCondition[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition",
     *      mappedBy="menuUpdate",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     */
    protected $menuUserAgentConditions;

    /**
     * @var array
     *
     * @ORM\Column(name="screens", type="array", nullable=true)
     */
    protected $screens = [];

    /**
     * @var ContentNode|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WebCatalogBundle\Entity\ContentNode")
     * @ORM\JoinColumn(name="content_node_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $contentNode;

    /**
     * @var string
     *
     * @ORM\Column(name="system_page_route", type="string", length=255, nullable=true)
     */
    protected $systemPageRoute;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->traitConstructor();

        $this->menuUserAgentConditions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtras()
    {
        $extras = [
            'image' => $this->getImage(),
            'screens' => $this->getScreens(),
            'condition' => $this->getCondition(),
            'divider' => $this->isDivider(),
            'userAgentConditions' => $this->getMenuUserAgentConditions(),
            'translate_disabled' => $this->getId() ? true : false,
            'content_node' => $this->getContentNode(),
            'system_page_route' => $this->getSystemPageRoute(),
        ];

        if ($this->getPriority() !== null) {
            $extras['position'] = $this->getPriority();
        }

        if ($this->getIcon() !== null) {
            $extras['icon'] = $this->getIcon();
        }

        return $extras;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     * @return MenuUpdate
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return MenuUserAgentCondition[]|Collection
     */
    public function getMenuUserAgentConditions()
    {
        return $this->menuUserAgentConditions;
    }

    /**
     * @param MenuUserAgentCondition $menuUserAgentCondition
     * @return MenuUpdate
     */
    public function addMenuUserAgentCondition(MenuUserAgentCondition $menuUserAgentCondition)
    {
        if (!$this->menuUserAgentConditions->contains($menuUserAgentCondition)) {
            $menuUserAgentCondition->setMenuUpdate($this);
            $this->menuUserAgentConditions->add($menuUserAgentCondition);
        }

        return $this;
    }

    /**
     * @param MenuUserAgentCondition $menuUserAgentCondition
     * @return MenuUpdate
     */
    public function removeMenuUserAgentCondition(MenuUserAgentCondition $menuUserAgentCondition)
    {
        if ($this->menuUserAgentConditions->contains($menuUserAgentCondition)) {
            $this->menuUserAgentConditions->removeElement($menuUserAgentCondition);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getScreens()
    {
        return (array) $this->screens;
    }

    /**
     * @param array $screens
     * @return MenuUpdate
     */
    public function setScreens(array $screens)
    {
        $this->screens = $screens;

        return $this;
    }

    /**
     * @param ContentNode|null $contentNode
     *
     * @return self
     */
    public function setContentNode(?ContentNode $contentNode): self
    {
        $this->contentNode = $contentNode;

        return $this;
    }

    /**
     * @return ContentNode|null
     */
    public function getContentNode(): ?ContentNode
    {
        return $this->contentNode;
    }

    /**
     * @param string|null $systemPageRoute
     *
     * @return MenuUpdate
     */
    public function setSystemPageRoute(?string $systemPageRoute): self
    {
        $this->systemPageRoute = $systemPageRoute;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSystemPageRoute(): ?string
    {
        return $this->systemPageRoute;
    }
}
