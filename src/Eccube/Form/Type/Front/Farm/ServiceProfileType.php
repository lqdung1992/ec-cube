<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/04/2018
 * Time: 11:02 AM
 */
namespace Eccube\Form\Type\Front\Farm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceProfileType extends AbstractType
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
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Customer',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'farmer_profile';
    }
}
