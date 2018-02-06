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
            ->add('customer_role', 'entity', array(
                'required' => true,
                'class' => 'Eccube\Entity\Master\CustomerRole',
                'empty_value' => '----------',
                'property' => 'name_jp',
                'label' => '申し込み分類',
                'constraints' => array(
                    new Assert\NotBlank(),
                )
            ))
            ->add('name01', 'text', array(
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
            ->add('tel01', 'text', array(
                'required' => true,
                'label' => '電話番号',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'numeric', 'message' => 'form.type.numeric.invalid')),
                    new Assert\Length(array(
                        'max' => 15,
                        'min' => 5,
                    )),
                ),
            ))
            ->add('email', 'repeated_email')
            ->add('password', 'repeated_password')
            ->add('bus_stop', 'entity', array(
                'required' => false,
                'class' => 'Eccube\Entity\Master\BusStop',
                'empty_value' => '----------',
                'label' => '集荷先のバス停'
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
        return 'farmer_regist';
    }
}
