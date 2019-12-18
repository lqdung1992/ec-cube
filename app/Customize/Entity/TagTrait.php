<?php

/**
 * @author Dung Le (Le Quoc Dung)
 * @email lqdung1992@gmail.com
 * @github https://github.com/lqdung1992
 * @date 12/16/2019
 */
namespace Customize\Entity;


use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * Demo to change ec4 core attribute
 *
 * @package Customize\Entity
 * @EntityExtension("Eccube\Entity\Tag")
 */
trait TagTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     */
    protected $name;
}
