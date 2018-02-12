<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/11/2018
 * Time: 19:39
 */
namespace Eccube\Form\Type\Front\Farm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FarmVoiceType extends AbstractType
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
            ->add('comment', 'textarea', array(
                'required' => true,
                'label' => 'Comment',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(
                        array(
                            'max' => $this->config['mtext_len'],
                        )
                    )
                )
            ))
            ->add('file_name', 'file', array(
                'required' => false,
                'constraints' => array(
                    new Assert\File(array(
                        'maxSize' => $this->config['image_size'] . 'k',
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
            'data_class' => 'Eccube\Entity\CustomerVoice',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'farm_voice';
    }
}
