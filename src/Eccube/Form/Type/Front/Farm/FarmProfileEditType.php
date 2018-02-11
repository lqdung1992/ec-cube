<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/11/2018
 * Time: 11:02 AM
 */
namespace Eccube\Form\Type\Front\Farm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FarmProfileEditType extends AbstractType
{
    protected $config;

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
            ->add('note', 'textarea', array(
                'required' => true,
                'label' => 'Note',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(
                        array(
                            'max' => $this->config['smtext_len'],
                        )
                    )
                )
            ))
            ->add('profile_image', 'file', array(
                'required' => false,
                'constraints' => array(
                    new Assert\File(array(
                        'maxSize' => $this->config['image_size'].'k',
                        'mimeTypes' => 'image/*'
                    ))
                )
            ))
            ->add('customer_image', 'file', array(
                'label' => 'Cover',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ))
            ->add('images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('add_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('delete_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'farm_profile_edit';
    }
}
