<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/15/2018
 * Time: 8:08 PM
 */

namespace Eccube\Form\Type\Front\Receiver;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReceiverSearch
 * @package Eccube\Form\Type\Front\Receiver
 */
class ReceiverSearchType extends AbstractType
{
    /**
     * @var array
     */
    protected $config;

    /**
     * ReceiverSearch constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'search', array(
                'required' => false,
            ))
            ->add('search_type', 'hidden', array(
                'required' => false
            ))
            ->add('farmer', 'hidden')
            ->add('method', 'hidden');

        $builder->add('pageno', 'hidden');
        $builder->add('orderby', 'product_list_order_by');
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

    public function getName()
    {
        return 'receiver_search';
    }

}