<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/4/2018
 * Time: 4:57 PM
 */

namespace Eccube\Form\Type\Front\Farm;

use Eccube\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HomeCompleteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order_by', 'choice', array(
                'choices' => array(
                    Order::SORT_BY_NEW => '新着順',
                    Order::SORT_BY_TOTAL => 'お届け先順'
                ),
                'multiple' => false,
                'expanded' => false,
                'empty_data' => 1,
                'constraints' => array()
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'home_complete';
    }
}
