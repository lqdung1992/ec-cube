<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/13/2018
 * Time: 4:28 PM
 */

namespace Eccube\Form\Type\Front\Farm;

use Eccube\Form\DataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ItemClassType extends AbstractType
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;

        $builder
            ->add('code', 'text', array(
                'label' => '商品コード',
                'required' => false,
            ))
            ->add('stock', 'number', array(
                'label' => '在庫数',
                'required' => false,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    )),
                ),
            ))
            ->add('sale_limit', 'number', array(
                'label' => '販売制限数',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 10,
                    )),
                    new Assert\GreaterThanOrEqual(array(
                        'value' => 1,
                    )),
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    )),
                ),
            ))
            ->add('price01', 'money', array(
                'label' => '通常価格',
                'currency' => 'JPY',
                'precision' => 0,
                'scale' => 0,
                'grouping' => true,
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 10,
                    )),
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    )),
                ),
            ))
            ->add('price02', 'money', array(
                'label' => '販売価格',
                'currency' => 'JPY',
                'precision' => 0,
                'scale' => 0,
                'grouping' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => 10,
                    )),
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    )),
                ),
            ))
            ->add('tax_rate', 'text', array(
                'label' => '消費税率',
                'required' => false,
                'constraints' => array(
                    new Assert\Range(array('min' => 0, 'max' => 100)),
                    new Assert\Regex(array(
                        'pattern' => "/^\d+(\.\d+)?$/",
                        'message' => 'form.type.float.invalid'
                    )),
                ),
            ))
            ->add('delivery_fee', 'money', array(
                'label' => '商品送料',
                'currency' => 'JPY',
                'precision' => 0,
                'scale' => 0,
                'grouping' => true,
                'required' => false,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    )),
                ),
            ))
            ->add('product_type', 'product_type', array(
                'label' => '商品種別',
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('delivery_date', 'delivery_date', array(
                'label' => 'お届け可能日',
                'required' => false,
                'empty_value' => '指定なし',
            ))
            ->add('add', 'checkbox', array(
                'label' => false,
                'required' => false,
                'value' => 1,
            ))
            ->addEventListener(FormEvents::POST_SUBMIT, function ($event) {
                $form = $event->getForm();
                $data = $form->getData();

                if (empty($data['stock_unlimited']) && is_null($data['stock'])) {
                    $form['stock_unlimited']->addError(new FormError('在庫数を入力、もしくは在庫無制限を設定してください。'));
                }
            });

        $transformer = new DataTransformer\IntegerToBooleanTransformer();

        $builder
            ->add($builder->create('stock_unlimited', 'checkbox', array(
                'label' => '無制限',
                'value' => '1',
                'required' => false,
            ))->addModelTransformer($transformer));


        $transformer = new DataTransformer\EntityToIdTransformer(
            $app['orm.em'],
            '\Eccube\Entity\ClassCategory'
        );
        $builder
            ->add($builder->create('ClassCategory1', 'hidden')
                ->addModelTransformer($transformer)
            )
            ->add($builder->create('ClassCategory2', 'hidden')
                ->addModelTransformer($transformer)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\ProductClass',
        ));
    }

    public function getName()
    {
        return 'item_class';
    }

}