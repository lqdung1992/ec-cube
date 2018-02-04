<?php

namespace Eccube\Form\Type\Front\Farm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceSignUpType extends AbstractType
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
            ->add('maker', 'entity', array(
                'required' => false,
                'class' => 'Eccube\Entity\Master\Job',
                'empty_value' => '生産者',
                'label' => '申し込み分類'
            ))
            ->add('name', 'text', array(
                'required' => true,
                'label' => '登録者名',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->config['stext_len'],
                    )),
                ),
            ))
            ->add('company_name', 'text', array(
                'required' => true,
                'label' => '法人名・屋号',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->config['stext_len'],
                    )),
                ),
            ))
            ->add('zip', 'zip', array(
//                'label' => '郵便番号'
            ))
//            ->add('address', 'address')
            ->add('addr01', 'text', array(
                'required' => true,
                'label' => '住所',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->config['stext_len'],
                    )),
                ),
            ))
            ->add('addr02', 'text', array(
                'required' => true,
                'label' => '電話番号',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->config['stext_len'],
                    )),
                ),
            ))
            ->add('tel', 'text', array(
                'required' => true,
                'label' => '電話番号'
            ))
            ->add('email', 'repeated_email');
//            ->add('password', 'repeated_password');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Farmer',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'farmer_regist';
    }
}
