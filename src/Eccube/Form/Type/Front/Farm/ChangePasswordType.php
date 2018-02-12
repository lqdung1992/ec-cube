<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 6:00 PM
 */

namespace Eccube\Form\Type\Front\Farm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePasswordType extends AbstractType
{
    /** @var array */
    private $config;

    /**
     * ChangePasswordType constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array(
            'invalid_message' => 'form.member.email.invalid',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Email(array('strict' => true)),
                    new Assert\Regex(array(
                        'pattern' => '/^[[:graph:][:space:]]+$/i',
                        'message' => 'form.type.graph.invalid',
                    )),
                ),
        ));
        $builder->add('old_password', 'password', array(
            'label' => '現在のパスワード',
            'required' => true,
            'error_bubbling' => false,
            'invalid_message' => 'form.member.password.invalid',
            'constraints' => array(
                new Assert\NotBlank(),
                new SecurityAssert\UserPassword(),
                new Assert\Length(array(
                    'min' => $this->config['password_min_len'],
                    'max' => $this->config['password_max_len'],
                )),
                new Assert\Regex(array(
                    'pattern' => '/^[[:graph:][:space:]]+$/i',
                    'message' => 'form.type.graph.invalid',
                )),
            ),
        ));
        $builder->add('new_password', 'repeated_password', array(
            'first_options'  => array(
                'label' => '新しいパスワード',
            ),
            'second_options' => array(
                'label' => '新しいパスワード（確認用）',
            ),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\ChangePassword',
        ));
    }

    public function getName()
    {
        return 'change_password';
    }
}